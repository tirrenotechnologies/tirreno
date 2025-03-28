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

class Logbook extends \Models\BaseSql {
    use \Traits\Enrichment\TimeZones;

    protected $DB_TABLE_NAME = 'event_logbook';

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
                event_logbook.raw_time,
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

        $fields = ['raw_time'];

        $results = array_map(function ($row) use ($fields) {
            try {
                $this->translateTimeZone($row, $fields, true);
            } catch (\Throwable $e) {
            }

            return $row;
        }, $results);

        return $results[0] ?? [];
    }

    public function rotateRequests(?int $apiKey): int {
        $params = [
            ':key'      => $apiKey,
            ':limit'    => \Utils\Constants::get('LOGBOOK_LIMIT'),
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
