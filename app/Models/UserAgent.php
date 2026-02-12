<?php

/**
 * tirreno ~ open-source security framework
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

namespace Tirreno\Models;

class UserAgent extends \Tirreno\Models\BaseSql implements \Tirreno\Interfaces\ApiKeyAccessAuthorizationInterface {
    protected ?string $DB_TABLE_NAME = 'event_ua_parsed';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $query = (
            'SELECT
                event_ua_parsed.id

            FROM
                event_ua_parsed

            WHERE
                event_ua_parsed.id  = :ua_id AND
                event_ua_parsed.key = :api_key'
        );

        $params = [
            ':api_key' => $apiKey,
            ':ua_id' => $subjectId,
        ];

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getFullUserAgentInfoById(int $uaId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':ua_id' => $uaId,
        ];

        $query = (
            'SELECT
                event_ua_parsed.id,
                event_ua_parsed.device,
                event_ua_parsed.device AS title,
                event_ua_parsed.browser_name,
                event_ua_parsed.browser_version,
                event_ua_parsed.os_name,
                event_ua_parsed.os_version,
                event_ua_parsed.ua,
                event_ua_parsed.modified,
                event_ua_parsed.checked
            FROM
                event_ua_parsed

            WHERE
                event_ua_parsed.key = :api_key AND
                event_ua_parsed.id  = :ua_id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function extractById(int $entityId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $entityId,
        ];

        $query = (
            "SELECT
                COALESCE(event_ua_parsed.ua, '') AS value

            FROM
                event_ua_parsed

            WHERE
                event_ua_parsed.id = :id AND
                event_ua_parsed.key = :api_key

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function getTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                event_device.user_agent AS id,
                COUNT(*) AS total_account
            FROM event_device
            WHERE
                event_device.user_agent IN ({$flatIds}) AND
                key = :key
            GROUP BY event_device.user_agent"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_account'] = $indexedResult[$item['id']]['total_account'];
            $res[$idx] = $item;
        }

        return $res;
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event_device.user_agent AS id,
                COUNT(DISTINCT(event.account)) AS cnt
            FROM event
            LEFT JOIN event_device
            ON event.device = event_device.id
            WHERE
                event_device.user_agent IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event_device.user_agent"
        );

        $totalAccount = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = [
                'total_account' => 0,
            ];
        }

        foreach ($totalAccount as $rec) {
            $result[$rec['id']]['total_account'] = $rec['cnt'];
        }

        return $result;
    }
}
