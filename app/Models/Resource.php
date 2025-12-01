<?php

/**
 * tirreno ~ open security analytics
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

declare(strict_types=1);

namespace Models;

class Resource extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event';

    public function getResourceById(int $resourceId): array {
        $query = (
            'SELECT
                url,
                lastseen,
                title

            FROM
                event_url

            WHERE
                event_url.id = :resource_id'
        );

        $params = [
            ':resource_id' => $resourceId,
        ];

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':api_key' => $apiKey,
            ':resource_id' => $subjectId,
        ];

        $query = (
            'SELECT
                event_url.id

            FROM
                event_url

            WHERE
                event_url.id = :resource_id AND
                event_url.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event.url AS id,
                COUNT(*) AS cnt
            FROM event
            WHERE
                event.url IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.url"
        );

        $totalVisit = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event.url AS id,
                COUNT(DISTINCT(event.account)) AS cnt
            FROM event
            WHERE
                event.url IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.url"
        );

        $totalAccount = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event.url AS id,
                COUNT(DISTINCT(event.ip)) AS cnt
            FROM event
            WHERE
                event.url IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.url"
        );

        $totalIp = $this->execQuery($query, $params);

        $params[':field_edit'] = \Utils\Constants::FIELD_EDIT_EVENT_TYPE_ID;
        $query = (
            "SELECT
                event.url   AS id,
                COUNT(*)    AS cnt
            FROM event
            WHERE
                event.url IN ({$flatIds}) AND
                event.type = :field_edit AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.url"
        );

        $totalEdit = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = [
                'total_visit'   => 0,
                'total_account' => 0,
                'total_ip'      => 0,
                'total_edit'    => 0,
            ];
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

        foreach ($totalEdit as $rec) {
            $result[$rec['id']]['total_edit'] = $rec['cnt'];
        }

        return $result;
    }

    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':field_edit'] = \Utils\Constants::FIELD_EDIT_EVENT_TYPE_ID;
        $extraClause = $force ? '' : ' AND event_url.lastseen >= event_url.updated';

        $query = (
            "UPDATE event_url
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                total_ip = COALESCE(sub.total_ip, 0),
                total_device = COALESCE(sub.total_device, 0),
                total_country = COALESCE(sub.total_country, 0),
                total_edit = COALESCE(sub.total_edit, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.url,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT event.account) AS total_account,
                    COUNT(DISTINCT event.ip) AS total_ip,
                    COUNT(DISTINCT event.device) AS total_device,
                    COUNT(DISTINCT event_ip.country) AS total_country,
                    COUNT(CASE WHEN event.type = :field_edit THEN TRUE END) AS total_edit
                FROM event
                LEFT JOIN event_ip ON event.ip = event_ip.id
                WHERE
                    event.url IN ($flatIds) AND
                    event.key = :key
                GROUP BY event.url
            ) AS sub
            RIGHT JOIN event_url sub_url ON sub.url = sub_url.id
            WHERE
                event_url.id = sub_url.id AND
                event_url.id IN ($flatIds) AND
                event_url.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    public function updateAllTotals(int $apiKey): int {
        $params = [
            ':key'          => $apiKey,
            ':field_edit'   => \Utils\Constants::FIELD_EDIT_EVENT_TYPE_ID,
        ];

        $query = (
            'UPDATE event_url
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                total_ip = COALESCE(sub.total_ip, 0),
                total_device = COALESCE(sub.total_device, 0),
                total_country = COALESCE(sub.total_country, 0),
                total_edit = COALESCE(sub.total_edit, 0),
                updated = date_trunc(\'milliseconds\', now())
            FROM (
                SELECT
                    event.url,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT event.account) AS total_account,
                    COUNT(DISTINCT event.ip) AS total_ip,
                    COUNT(DISTINCT event.device) AS total_device,
                    COUNT(DISTINCT event_ip.country) AS total_country,
                    COUNT(CASE WHEN event.type = :field_edit THEN TRUE END) AS total_edit
                FROM event
                LEFT JOIN event_ip ON event.ip = event_ip.id
                WHERE
                    event.key = :key
                GROUP BY event.url
            ) AS sub
            RIGHT JOIN event_url sub_url ON sub.url = sub_url.id
            WHERE
                event_url.id = sub_url.id AND
                event_url.key = :key AND
                event_url.lastseen >= event_url.updated'
        );

        return $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                total_ip,
                total_visit,
                total_edit,
                total_account
            FROM event_url
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_ip'] = $indexedResult[$item['id']]['total_ip'];
            $item['total_edit'] = $indexedResult[$item['id']]['total_edit'];
            $item['total_visit'] = $indexedResult[$item['id']]['total_visit'];
            $item['total_account'] = $indexedResult[$item['id']]['total_account'];
            $res[$idx] = $item;
        }

        return $res;
    }
}
