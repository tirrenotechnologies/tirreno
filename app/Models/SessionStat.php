<?php

/**
 * Tirreno ~ Open source user analytics
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

namespace Models;

class SessionStat extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_session_stat';

    public function updateTotalsByAccountIds(array $ids, int $apiKey): ?int {
        if (!count($ids)) {
            return 0;
        }

        $cnt = 0;
        $batchSize = 100;

        foreach (array_chunk($ids, $batchSize) as $accounts) {
            [$params, $flatIds] = $this->getArrayPlaceholders($accounts);
            $params[':key'] = $apiKey;

            $query = ("
                SELECT
                    event_session.id

                FROM
                    event_session

                LEFT JOIN event_session_stat
                ON event_session.id = event_session_stat.session_id

                WHERE
                    (event_session_stat.completed IS NULL OR
                    event_session_stat.completed IS FALSE) AND
                    event_session.account_id IN ({$flatIds}) AND
                    event_session.key = :key
            ");


            $results = $this->execQuery($query, $params);

            if (!count($results)) {
                return null;
            }

            $sessionIds = array_column($results, 'id');
            $result = $this->updateStatsByIds($sessionIds, $apiKey);

            $cnt += $result ? $result : 0;
        }

        return $cnt;
    }

    public function updateStats(int $apiKey): ?int {
        $params = [
            ':key'  => $apiKey,
        ];


        // select only that sessions which events have not went under retention
        $query = ('
            SELECT
                COUNT(DISTINCT event.session_id) AS cnt
            FROM
                event

            LEFT JOIN event_session
            ON event_session.id = event.session_id

            LEFT JOIN event_session_stat
            ON event_session.id = event_session_stat.session_id

            WHERE
                (event_session_stat.completed IS NULL OR
                event_session_stat.completed IS FALSE) AND
                event_session.key = :key');

        $results = $this->execQuery($query, $params);

        $total = $results[0] ?? [];
        $total = $total['cnt'] ?? 0;

        if (!$total) {
            return null;
        }

        $query = ('
            SELECT
                DISTINCT event.session_id AS id
            FROM event

            LEFT JOIN event_session
            ON event_session.id = event.session_id

            LEFT JOIN event_session_stat
            ON event_session.id = event_session_stat.session_id

            WHERE
                (event_session_stat.completed IS NULL OR
                event_session_stat.completed IS FALSE) AND
                event_session.key = :key

            ORDER BY event.session_id DESC
            LIMIT :length OFFSET :offset
        ');

        $cnt = 0;
        $limit = 5000;

        for ($offset = 0; $offset < $total; $offset += $limit) {
            $params[':length'] = $limit;
            $params[':offset'] = $offset;
            $results = $this->execQuery($query, $params);
            if (!count($results)) {
                continue;
            }

            $sessionIds = array_column($results, 'id');
            $results = $this->updateStatsByIds($sessionIds, $apiKey);
            $cnt += $results ? $results : 0;
        }

        return $cnt;
    }

    public function updateStatsByIds(array $sessionIds, int $apiKey): ?int {
        $batchSize = 5000;

        $stats = [];
        $plcArr = [];
        $results = null;
        $params = [];
        $arrPlaceholders = [];

        foreach (array_chunk($sessionIds, $batchSize) as $ids) {
            $stats = [];
            $plcArr = [];
            $params = [
                ':key'  => $apiKey,
            ];

            foreach ($ids as $id) {
                $plcArr[] = ':id_' . strval($id);
                $params[':id_' . strval($id)] = $id;
            }
            $idsStr = '(' . implode(', ', $plcArr) . ')';

            $results = $this->getBaseSessionsData($idsStr, $params);

            if (!count($results)) {
                continue;
            }

            foreach ($results as $result) {
                $id = $result['id'];
                $stats[$id] = [
                    ':key_' . strval($id)              => $apiKey,
                    ':session_id_' . strval($id)       => $result['id'],
                    ':duration_' . strval($id)         => $result['duration'],
                    ':completed_' . strval($id)        => $result['completed'],
                    ':event_count_' . strval($id)      => $result['event_count'],
                    ':device_count_' . strval($id)     => $result['device_count'],
                    ':ip_count_' . strval($id)         => $result['ip_count'],
                    ':country_count_' . strval($id)    => $result['country_count'],
                    ':new_device_count_' . strval($id) => $result['new_device_count'],
                    ':new_ip_count_' . strval($id)     => $result['new_ip_count'],
                ];
            }

            $this->eventColumnStats('type', $idsStr, $params, $stats, ':event_types_');
            $this->eventColumnStats('http_code', $idsStr, $params, $stats);
            $this->eventColumnStats('http_method', $idsStr, $params, $stats);

            $arrPlaceholders = [];
            $params = [];

            foreach ($stats as $id => $item) {
                ksort($item);
                $arrPlaceholders[] = '(' . implode(', ', array_keys($item)) . ')';
                foreach ($item as $key => $val) {
                    $params[$key] = $val;
                }
            }

            $strPlaceholders = implode(', ', $arrPlaceholders);

            // column names sorted asc
            $query = ("
                INSERT INTO event_session_stat (
                    completed, country_count, device_count, duration, event_count, event_types,
                    http_codes, http_methods, ip_count, key, new_device_count, new_ip_count, session_id
                ) VALUES {$strPlaceholders}
                ON CONFLICT (session_id) DO UPDATE SET
                    updated = NOW(), ip_count = EXCLUDED.ip_count, device_count = EXCLUDED.device_count,
                    event_count = EXCLUDED.event_count, country_count = EXCLUDED.country_count,
                    new_ip_count = EXCLUDED.new_ip_count, new_device_count = EXCLUDED.new_device_count,
                    http_codes = EXCLUDED.http_codes, http_methods = EXCLUDED.http_methods,
                    event_types = EXCLUDED.event_types, completed = EXCLUDED.completed
            ");

            $this->execQuery($query, $params);
        }

        return count($sessionIds);
    }

    private function getBaseSessionsData(string $idsPlaceholder, array &$params): array {
        $query = ("
            SELECT
                event_session.id                                AS id,
                EXTRACT(EPOCH FROM (event_session.lastseen - event_session.created))::integer AS duration,
                (
                    EXTRACT(EPOCH FROM(event_session.lastseen - event_session.created))::integer > 14400 OR
                    EXTRACT(EPOCH FROM(CURRENT_TIMESTAMP))::integer - 3600 > EXTRACT(EPOCH FROM(event_session.lastseen))::integer
                )::boolean                                      AS completed,
                COUNT(event.id)                                 AS event_count,
                COUNT(DISTINCT event.device)                    AS device_count,
                COUNT(DISTINCT event.ip)                        AS ip_count,
                COUNT(DISTINCT event_ip.country)                AS country_count,
                COUNT(DISTINCT CASE WHEN
                    event_device.created <= event_session.lastseen AND
                    event_device.created >= event_session.created
                    THEN event.device END)                      AS new_device_count,
                COUNT(DISTINCT CASE WHEN
                    event_ip.created <= event_session.lastseen AND
                    event_ip.created >= event_session.created
                    THEN event.ip END)                          AS new_ip_count

            FROM event_session

            LEFT JOIN event
            ON event_session.id = event.session_id

            LEFT JOIN event_device
            ON event.device = event_device.id

            LEFT JOIN event_ip
            ON event.ip = event_ip.id

            WHERE
                event_session.id IN {$idsPlaceholder} AND
                event.key = :key

            GROUP BY
                event_session.id,
                event_session.lastseen,
                event_session.created
        ");

        return $this->execQuery($query, $params);
    }

    private function eventColumnStats(string $column, string $idsPlaceholder, array &$params, array &$stats, ?string $alias = null): void {
        if (!in_array($column, ['http_code', 'http_method', 'type'])) {
            return;
        }

        if (!$alias) {
            $alias = ':' . $column . 's_';
        }

        $query = ("
            SELECT
                event.session_id    AS id,
                event.{$column}     AS value,
                COUNT(*)            AS cnt
            FROM
                event

            WHERE
                event.session_id IN {$idsPlaceholder} AND
                event.key = :key

            GROUP BY
                event.session_id,
                event.{$column}
        ");

        $results = $this->execQuery($query, $params);

        $data = [];
        foreach ($results as $result) {
            if ($result['value'] !== null) {
                if (!array_key_exists($result['id'], $data)) {
                    $data[$result['id']] = [];
                }
                $data[$result['id']][$result['value']] = $result['cnt'];
            }
        }

        foreach ($stats as $id => $item) {
            if (array_key_exists($id, $data)) {
                $stats[$id][$alias . strval($id)] = json_encode($data[$id], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
            } else {
                $stats[$id][$alias . strval($id)] = null;
            }
        }
    }
}
