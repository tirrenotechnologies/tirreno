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

class Bot extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_ua_parsed';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $query = (
            'SELECT
                event_ua_parsed.id

            FROM
                event_ua_parsed

            WHERE
                event_ua_parsed.key    = :api_key
                AND event_ua_parsed.id = :ua_id'
        );

        $params = [
            ':api_key' => $apiKey,
            ':ua_id' => $subjectId,
        ];

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getFullBotInfoById(int $uaId, int $apiKey): array {
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
                event_ua_parsed.key = :api_key
                AND event_ua_parsed.id = :id

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }
}
