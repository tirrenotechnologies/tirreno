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

namespace Models\Chart;

class Logbook extends Base {
    protected $DB_TABLE_NAME = 'event_logbook';

    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $timestamps = array_column($data, 'ts');
        $line1      = array_column($data, 'event_normal_type_count');
        $line2      = array_column($data, 'event_issued_type_count');
        $line3      = array_column($data, 'event_failed_type_count');

        return $this->addEmptyDays([$timestamps, $line1, $line2, $line3]);
    }

    private function getFirstLine(int $apiKey): array {
        $request = $this->f3->get('REQUEST');
        $dateRange = $this->getDatesRange($request);
        if (!$dateRange) {
            $dateRange = [
                'endDate'   => date('Y-m-d H:i:s'),
                'startDate' => date('Y-m-d H:i:s', 0),
            ];
        }

        //$dateRange['endDate']   = \Utils\TimeZones::localizeForActiveOperator($dateRange['endDate']);
        //$dateRange['startDate'] = \Utils\TimeZones::localizeForActiveOperator($dateRange['startDate']);

        [$failedTypesParams, $failedFlatIds]    = $this->getArrayPlaceholders(\Utils\Constants::FAILED_LOGBOOK_EVENT_TYPES, 'failed');
        [$issuedTypesParams, $issuedFlatIds]    = $this->getArrayPlaceholders(\Utils\Constants::ISSUED_LOGBOOK_EVENT_TYPES, 'issued');
        [$normalTypesParams, $normalFlatIds]    = $this->getArrayPlaceholders(\Utils\Constants::NORMAL_LOGBOOK_EVENT_TYPES, 'normal');
        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            ':resolution'   => $this->getResolution($request),
        ];
        $params = array_merge($params, $failedTypesParams);
        $params = array_merge($params, $issuedTypesParams);
        $params = array_merge($params, $normalTypesParams);

        $query = (
            "SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_logbook.started))::bigint  AS ts,
                COUNT(CASE WHEN event_error_type.value IN ({$normalFlatIds}) THEN TRUE END) AS event_normal_type_count,
                COUNT(CASE WHEN event_error_type.value IN ({$issuedFlatIds}) THEN TRUE END) AS event_issued_type_count,
                COUNT(CASE WHEN event_error_type.value IN ({$failedFlatIds}) THEN TRUE END) AS event_failed_type_count

            FROM
                event_logbook

            LEFT JOIN event_error_type
            ON event_logbook.error_type = event_error_type.id

            WHERE
                event_logbook.key = :api_key AND
                event_logbook.started >= :start_time AND
                event_logbook.started <= :end_time

            GROUP BY ts
            ORDER BY ts"
        );

        return $this->execQuery($query, $params);
    }
}
