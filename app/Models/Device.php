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

class Device extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_device';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $query = (
            'SELECT
                event_device.id

            FROM
                event_device

            WHERE
                event_device.id     = :device_id AND
                event_device.key    = :api_key'
        );

        $params = [
            ':api_key' => $apiKey,
            ':device_id' => $subjectId,
        ];

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getFullDeviceInfoById(int $deviceId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':device_id' => $deviceId,
        ];

        $query = (
            'SELECT
                event_device.id,
                event_device.lang,
                event_device.created,
                event_device.user_agent AS ua_id,
                event_ua_parsed.device,
                event_ua_parsed.browser_name,
                event_ua_parsed.browser_version,
                event_ua_parsed.os_name,
                event_ua_parsed.os_version,
                event_ua_parsed.ua,
                event_ua_parsed.checked,
                event_ua_parsed.modified
            FROM
                event_device
            LEFT JOIN event_ua_parsed
            ON (event_device.user_agent = event_ua_parsed.id)

            WHERE
                event_device.key    = :api_key AND
                event_device.id     = :device_id'
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
                event_ua_parsed.key = :api_key AND
                event_ua_parsed.id = :id
            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function updateAllTotals(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'UPDATE event_device
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                updated = date_trunc(\'milliseconds\', now())
            FROM (
                SELECT
                    event.device,
                    COUNT(*) AS total_visit
                    FROM event
                    WHERE
                        event.key = :key
                    GROUP BY event.device
            ) AS sub
            RIGHT JOIN event_device sub_device ON sub.device = sub_device.id
            WHERE
                event_device.id = sub_device.id AND
                event_device.key = :key AND
                event_device.lastseen >= event_device.updated'
        );

        return $this->execQuery($query, $params);
    }

    public function countNotChecked(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(*) AS count
            FROM event_ua_parsed
            WHERE
                event_ua_parsed.key = :key AND
                event_ua_parsed.checked IS FALSE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedExists(int $apiKey): bool {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT 1
            FROM event_ua_parsed
            WHERE
                event_ua_parsed.key = :key AND
                event_ua_parsed.checked IS FALSE
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
                event_ua_parsed.id
            FROM event_device
            LEFT JOIN event_ua_parsed ON event_device.user_agent = event_ua_parsed.id
            WHERE
                event_device.account_id = :user_id AND
                event_device.key = :api_key AND
                event_ua_parsed.checked IS FALSE'
        );

        return array_column($this->execQuery($query, $params), 'id');
    }
}
