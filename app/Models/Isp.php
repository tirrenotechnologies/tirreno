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

class Isp extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_isp';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':api_key' => $apiKey,
            ':isp_id' => $subjectId,
        ];

        $query = (
            'SELECT
                event_isp.id

            FROM
                event_isp

            WHERE
                event_isp.key = :api_key
                AND event_isp.id = :isp_id'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getIdByAsn(int $asn, int $apiKey): ?int {
        $params = [
            ':api_key' => $apiKey,
            ':asn' => $asn,
        ];

        $query = (
            'SELECT
                event_isp.id
            FROM event_isp
            WHERE
                event_isp.key = :api_key AND
                event_isp.asn = :asn
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function getIpCountById(int $ispId, int $apiKey): int {
        $params = [
            ':api_key' => $apiKey,
            ':ispid' => $ispId,
        ];

        $query = (
            'SELECT COUNT(*) AS count
            FROM event_ip
            WHERE
                event_ip.isp = :ispid AND
                event_ip.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function getFullIspInfoById(int $ispId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':ispid' => $ispId,
        ];

        $query = (
            'SELECT
                event_isp.asn,
                event_isp.name,
                event_isp.description,
                event_isp.total_visit,
                event_isp.total_account,
                event_isp.lastseen,
                (
                    SELECT COUNT(DISTINCT event.account)
                    FROM event

                    LEFT JOIN event_ip
                    ON event.ip = event_ip.id

                    LEFT JOIN event_account
                    ON event.account = event_account.id

                    WHERE
                        event.key = :api_key AND
                        event_ip.isp = :ispid AND
                        event_account.fraud is TRUE
                ) AS total_fraud

            FROM
                event_isp

            WHERE
                event_isp.key = :api_key
                AND event_isp.id = :ispid

            GROUP BY
                event_isp.id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function insertRecord(array $data, int $apiKey): int {
        $params = [
            ':key'          => $apiKey,
            ':asn'          => $data['asn'],
            ':name'         => $data['name'],
            ':description'  => $data['description'],
            ':lastseen'     => $data['lastseen'],
            ':created'      => $data['created'],
            ':updated'      => $data['lastseen'],
        ];

        $query = (
            'INSERT INTO event_isp (
                key, asn, name, description, lastseen, created, updated
            ) VALUES (
                :key, :asn, :name, :description, :lastseen, :created, :updated
            ) ON CONFLICT (key, asn) DO UPDATE SET
                name = EXCLUDED.name, description = EXCLUDED.description, lastseen = EXCLUDED.lastseen
            RETURNING id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'];
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event_ip.isp AS id,
                COUNT(*) AS cnt
            FROM event
            INNER JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.isp IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event_ip.isp"
        );

        $totalVisit = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event_ip.isp AS id,
                COUNT(DISTINCT(event.account)) AS cnt
            FROM event
            INNER JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.isp IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event_ip.isp"
        );

        $totalAccount = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event_ip.isp AS id,
                COUNT(*) AS cnt
            FROM event_ip
            WHERE
                event_ip.isp IN ({$flatIds}) AND
                event_ip.key = :key AND
                event_ip.lastseen > :start_date AND
                event_ip.lastseen < :end_date
            GROUP BY event_ip.isp"
        );

        $totalIp = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = ['total_visit' => 0, 'total_account' => 0, 'total_ip' => 0];
        }

        foreach ($totalVisit as $rec) {
            $result[$rec['id']]['total_visit'] = $rec['cnt'];
        }

        foreach ($totalAccount as $rec) {
            $result[$rec['id']]['total_account'] = $rec['cnt'];
        }

        foreach ($totalIp as $rec) {
            $result[$rec['id']]['total_ip'] = $rec['cnt'];
        }

        return $result;
    }

    public function updateTotalsByEntityIds(array $ids, int $apiKey): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;

        $query = (
            "UPDATE event_isp
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event_ip.isp,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS total_account
                FROM event
                LEFT JOIN event_ip
                ON event.ip = event_ip.id
                WHERE
                    event_ip.isp IN ({$flatIds}) AND
                    event.key = :key
                GROUP BY event_ip.isp
            ) AS sub
            RIGHT JOIN event_isp sub_isp ON sub.isp = sub_isp.id
            WHERE
                event_isp.key = :key AND
                event_isp.id = sub_isp.id AND
                event_isp.id IN ({$flatIds}) AND
                event_isp.lastseen >= event_isp.updated"
        );

        $this->execQuery($query, $params);
    }

    public function updateAllTotals(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];
        $query = (
            'UPDATE event_isp
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                updated = date_trunc(\'milliseconds\', now())
            FROM (
                SELECT
                    event_ip.isp,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS total_account
                FROM event
                LEFT JOIN event_ip ON event.ip = event_ip.id
                WHERE
                    event.key = :key
                GROUP BY event_ip.isp
            ) AS sub
            RIGHT JOIN event_isp sub_isp ON sub.isp = sub_isp.id
            WHERE
                event_isp.key = :key AND
                event_isp.id = sub_isp.id AND
                event_isp.lastseen >= event_isp.updated'
        );

        return $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                total_visit,
                total_account
            FROM event_isp
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_visit'] = $indexedResult[$item['id']]['total_visit'];
            $item['total_account'] = $indexedResult[$item['id']]['total_account'];
            $res[$idx] = $item;
        }

        return $res;
    }
}
