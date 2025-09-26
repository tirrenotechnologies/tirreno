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

class Events extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $timestamps = array_column($data, 'ts');
        $line1      = array_column($data, 'event_normal_type_count');
        $line2      = array_column($data, 'event_editing_type_count');
        $line3      = array_column($data, 'event_alert_type_count');
        $line4      = array_column($data, 'unauthorized_event_count');

        return $this->addEmptyDays([$timestamps, $line1, $line2, $line3, $line4]);
    }

    private function getFirstLine(int $apiKey): array {
        $request = $this->f3->get('REQUEST');
        $dateRange = $this->getDatesRange($request);
        if (!$dateRange) {
            $dateRange = [
                'endDate' => date('Y-m-d H:i:s'),
                'startDate' => date('Y-m-d H:i:s', 0),
            ];
        }
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();
        [$alertTypesParams, $alertFlatIds]      = $this->getArrayPlaceholders(\Utils\Constants::get('ALERT_EVENT_TYPES'), 'alert');
        [$editTypesParams, $editFlatIds]        = $this->getArrayPlaceholders(\Utils\Constants::get('EDITING_EVENT_TYPES'), 'edit');
        [$normalTypesParams, $normalFlatIds]    = $this->getArrayPlaceholders(\Utils\Constants::get('NORMAL_EVENT_TYPES'), 'normal');
        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            ':resolution'   => $this->getResolution($request),
            ':offset'       => strval($offset),
            ':unauth'       => \Utils\Constants::get('UNAUTHORIZED_USERID'),
        ];
        $params = array_merge($params, $alertTypesParams);
        $params = array_merge($params, $editTypesParams);
        $params = array_merge($params, $normalTypesParams);

        $query = (
            "SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(CASE WHEN event.type IN ({$normalFlatIds})  THEN TRUE END) AS event_normal_type_count,
                COUNT(CASE WHEN event.type IN ({$editFlatIds})    THEN TRUE END) AS event_editing_type_count,
                COUNT(CASE WHEN event.type IN ({$alertFlatIds})   THEN TRUE END) AS event_alert_type_count,
                COUNT(CASE WHEN event_account.userid = :unauth    THEN TRUE END) AS unauthorized_event_count

            FROM
                event

            LEFT JOIN event_account
            ON event.account = event_account.id

            WHERE
                event.key = :api_key AND
                event.time >= :start_time AND
                event.time <= :end_time

            GROUP BY ts
            ORDER BY ts"
        );

        return $this->execQuery($query, $params);
    }
}
