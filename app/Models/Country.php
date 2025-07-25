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

class Country extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_country';

    public function getCountryById(int $countryId, int $apiKey): array {
        $params = [
            ':api_key'      => $apiKey,
            ':country_id'   => $countryId,
        ];

        $query = (
            'SELECT
                countries.iso,
                countries.value

            FROM
                event_country

            INNER JOIN countries
            ON (event_country.country = countries.id)

            WHERE
                event_country.key = :api_key
                AND event_country.country = :country_id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function getCountryIdByIso(string $countryIso): int {
        $params = [
            ':country_iso' => $countryIso,
        ];

        $query = (
            'SELECT
                countries.id

            FROM
                countries

            WHERE
               countries.iso = :country_iso'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? 0;
    }

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':api_key'      => $apiKey,
            ':country_id'   => $subjectId,
        ];

        $query = (
            'SELECT
                event_country.country

            FROM
                event_country

            WHERE
                event_country.key = :api_key
                AND event_country.country = :country_id'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function insertRecord(array $data, int $apiKey): int {
        $params = [
            ':key'      => $apiKey,
            ':country'  => $data['id'],
            ':lastseen' => $data['lastseen'],
            ':updated'  => $data['lastseen'],
        ];

        $query = (
            'INSERT INTO event_country (
                key, country, lastseen, updated
            ) VALUES (
                  :key, :country, :lastseen, :updated
            ) ON CONFLICT (country, key) DO UPDATE SET
                lastseen = EXCLUDED.lastseen
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
                event_ip.country AS id,
                COUNT(*) AS cnt
            FROM event
            INNER JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.country IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event_ip.country"
        );

        $totalVisit = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event_ip.country AS id,
                COUNT(DISTINCT(event.account)) AS cnt
            FROM event
            INNER JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.country IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event_ip.country"
        );

        $totalAccount = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event_ip.country AS id,
                COUNT(*) AS cnt
            FROM event_ip
            WHERE
                event_ip.country IN ({$flatIds}) AND
                event_ip.key = :key AND
                event_ip.lastseen > :start_date AND
                event_ip.lastseen < :end_date
            GROUP BY event_ip.country"
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

    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $extraClause = $force ? '' : ' AND event_country.lastseen >= event_country.updated';

        $query = (
            "UPDATE event_country
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                total_ip = COALESCE(sub.total_ip, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event_ip.country,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT event.account) AS total_account,
                    COUNT(DISTINCT event.ip) AS total_ip
                FROM event
                JOIN event_ip ON event.ip = event_ip.id
                WHERE
                    event_ip.country IN ($flatIds) AND
                    event.key = :key
                GROUP BY event_ip.country
            ) AS sub
            RIGHT JOIN countries sub_country ON sub.country = sub_country.id
            WHERE
                event_country.country = sub_country.id AND
                event_country.country IN ($flatIds) AND
                event_country.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    public function updateAllTotals(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];
        $query = (
            'UPDATE event_country
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                total_ip = COALESCE(sub.total_ip, 0),
                updated = date_trunc(\'milliseconds\', now())
            FROM (
                SELECT
                    event_ip.country,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT event.account) AS total_account,
                    COUNT(DISTINCT event.ip) AS total_ip
                FROM event
                JOIN event_ip ON event.ip = event_ip.id AND event.key = event_ip.key
                WHERE event.key = :key
                GROUP BY event_ip.country
            ) AS sub
            RIGHT JOIN countries sub_country ON sub.country = sub_country.id
            WHERE
                event_country.key = :key AND
                event_country.country = sub_country.id AND
                event_country.lastseen >= event_country.updated'
        );

        return $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                country AS id,
                total_ip,
                total_visit,
                total_account
            FROM event_country
            WHERE country IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_ip'] = $indexedResult[$item['id']]['total_ip'];
            $item['total_visit'] = $indexedResult[$item['id']]['total_visit'];
            $item['total_account'] = $indexedResult[$item['id']]['total_account'];
            $res[$idx] = $item;
        }

        return $res;
    }
}
