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

class Ip extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface, \Interfaces\FraudFlagUpdaterInterface {
    protected $DB_TABLE_NAME = 'event_ip';

    public function getIpById(int $ipId): ?string {
        $info = $this->getFullIpInfoById($ipId);

        return $info['ip'] ?? null;
    }

    public function getIdByValue(string $ip, int $apiKey): ?int {
        $params = [
            ':ip_value' => $ip,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_ip.id
            FROM
                event_ip
            WHERE
                event_ip.key = :api_key
                AND event_ip.ip = :ip_value'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function getFullIpInfoById(int $ipId): array {
        $params = [
            ':ipid' => $ipId,
        ];

        $query = (
            'SELECT
                event_ip.id,
                event_ip.ip,
                event_ip.cidr,
                event_ip.lastseen,
                event_ip.created,
                event_ip.ip AS title,
                event_ip.isp AS ispid,
                event_ip.data_center,
                event_ip.relay,
                event_ip.starlink,
                event_ip.vpn,
                event_ip.tor,
                event_ip.fraud_detected,
                event_ip.blocklist,
                event_ip.checked,

                event_isp.asn,
                event_isp.name,
                event_isp.description,

                countries.value AS full_country,
                countries.id    AS country_id,
                countries.iso   AS country_iso

            FROM
                event_ip

            LEFT JOIN event_isp
            ON (event_ip.isp = event_isp.id)

            INNER JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event_ip.id = :ipid'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':ip_id' => $subjectId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_ip.id

            FROM
                event_ip

            WHERE
                event_ip.key = :api_key
                AND event_ip.id = :ip_id'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function updateFraudFlag(array $ids, bool $fraud, int $apiKey): void {
        if (!count($ids)) {
            return;
        }

        [$params, $placeHolders] = $this->getArrayPlaceholders($ids);

        $params[':fraud'] = $fraud;
        $params[':api_key'] = $apiKey;

        $query = (
            "UPDATE event_ip
                SET fraud_detected = :fraud

            WHERE
                key = :api_key
                AND id IN ({$placeHolders})"
        );

        $this->execQuery($query, $params);
    }

    public function extractById(int $entityId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $entityId,
        ];

        $query = (
            "SELECT
                split_part(COALESCE(event_ip.ip::text, ''), '/', 1) AS value,
                event_ip.hash AS hash

            FROM
                event_ip

            WHERE
                event_ip.key = :api_key
                AND event_ip.id = :id

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event.ip AS id,
                COUNT(*) AS cnt
            FROM event
            WHERE
                event.ip IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.ip"
        );

        $totalVisit = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = ['total_visit' => 0];
        }

        foreach ($totalVisit as $rec) {
            $result[$rec['id']]['total_visit'] = $rec['cnt'];
        }

        return $result;
    }

    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $extraClause = $force ? '' : ' AND event_ip.lastseen >= event_ip.updated';

        $query = (
            "UPDATE event_ip
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                shared = COALESCE(sub.shared, 1),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.ip,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS shared
                FROM event
                WHERE
                    event.ip IN ($flatIds) AND
                    event.key = :key
                GROUP BY event.ip
            ) AS sub
            RIGHT JOIN event_ip sub_ip ON sub.ip = sub_ip.id
            WHERE
                event_ip.id = sub_ip.id AND
                event_ip.id IN ($flatIds) AND
                event_ip.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    public function updateTotalsByAccountIds(array $ids, int $apiKey): int {
        if (!count($ids)) {
            return 0;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;

        $idsQuery = (
            "SELECT
                DISTINCT event.ip
            FROM event
            WHERE
                event.account IN ($flatIds) AND
                event.key = :key"
        );

        $query = (
            "UPDATE event_ip
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                shared = COALESCE(sub.shared, 1),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.ip,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS shared
                FROM event
                WHERE
                    event.ip IN ($idsQuery) AND
                    event.key = :key
                GROUP BY event.ip
            ) AS sub
            RIGHT JOIN event_ip sub_ip ON sub.ip = sub_ip.id
            WHERE
                event_ip.id = sub.ip AND
                event_ip.id IN ($idsQuery) AND
                event_ip.key = :key AND
                event_ip.lastseen >= event_ip.updated"
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
                shared AS total_account
            FROM event_ip
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

    public function countNotChecked(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        // count only ips appearing in events (not overriden by retention)
        $query = (
            'SELECT
                COUNT(DISTINCT event_ip.id) AS count
            FROM event
            LEFT JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.key = :key AND
                event_ip.checked IS FALSE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedExists(int $apiKey): bool {
        $params = [
            ':key' => $apiKey,
        ];

        // count only ips appearing in events (not overriden by retention)
        $query = (
            'SELECT 1
            FROM event
            LEFT JOIN event_ip
            ON event.ip = event_ip.id
            WHERE
                event_ip.key = :key AND
                event_ip.checked IS FALSE
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return (bool) count($results);
    }

    public function notCheckedForUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];

        $query = (
            'SELECT DISTINCT
                event_ip.id
            FROM event
            LEFT JOIN event_ip ON event.ip = event_ip.id
            WHERE
                event.account = :user_id AND
                event.key = :api_key AND
                event_ip.checked IS FALSE'
        );

        return array_column($this->execQuery($query, $params), 'id');
    }
}
