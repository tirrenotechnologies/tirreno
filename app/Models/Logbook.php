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

class Logbook extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_logbook';

    public function getLastSucceededEvent(int $apiKey): array {
        $params = [
            ':api_key'          => $apiKey,
            ':endpoint'         => '/sensor/',
            ':success'          => \Utils\Constants::LOGBOOK_ERROR_TYPE_SUCCESS,
            ':validation_error' => \Utils\Constants::LOGBOOK_ERROR_TYPE_VALIDATION_ERROR,
        ];

        $query = (
            'SELECT
                event_logbook.event,
                event_logbook.ended     AS lastseen

            FROM
                event_logbook

            WHERE
                event_logbook.key = :api_key AND
                (
                    event_logbook.error_type = :success  OR
                    event_logbook.error_type = :validation_error
                ) AND
                event_logbook.endpoint = :endpoint
            ORDER BY event_logbook.ended DESC
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function getLogbookDetails(int $id, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $id,
        ];

        $query = (
            'SELECT
                event_logbook.id,
                event_logbook.ip,
                event_logbook.raw,
                event_logbook.started,
                event_logbook.endpoint,
                event_logbook.error_text,
                event_logbook.error_type,
                event_error_type.name           AS error_name,
                event_error_type.value          AS error_value

            FROM
                event_logbook

            LEFT JOIN event_error_type
            ON (event_logbook.error_type = event_error_type.id)

            WHERE
                event_logbook.id = :id AND
                event_logbook.key = :api_key
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function add(
        string $ip,
        string $endpoint,
        ?int $event,
        int $errorType,
        ?string $errorText,
        string $raw,
        string $started,
        int $apiKey
    ): void {
        $params = [
            ':ip'           => $ip,
            ':endpoint'     => $endpoint,
            ':event'        => $event,
            ':error_type'   => $errorType,
            ':error_text'   => $errorText,
            ':raw'          => $raw,
            ':started'      => $started,
            ':key'          => $apiKey,
        ];

        $query = (
            'INSERT INTO event_logbook
                (endpoint, key, ip, event, error_type, error_text, raw, started)
            VALUES
                (:endpoint, :key, :ip, :event, :error_type, :error_text, :raw, :started)'
        );

        $this->execQuery($query, $params);
    }

    public function rotateRequests(?int $apiKey): int {
        $params = [
            ':key'      => $apiKey,
            ':limit'    => \Utils\Variables::getLogbookLimit(),
        ];

        $query = (
            'SELECT
                id
            FROM event_logbook
            WHERE key = :key
            ORDER BY id DESC
            LIMIT 1 OFFSET :limit'
        );

        $result = $this->execQuery($query, $params);

        if (!count($result)) {
            return 0;
        }

        $params = [
            ':id' => $result[0]['id'],
            ':key' => $apiKey,
        ];

        $query = (
            'DELETE FROM event_logbook
            WHERE
                id < :id AND
                key = :key'
        );

        return $this->execQuery($query, $params);
    }
}
