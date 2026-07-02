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

namespace Tirreno\Models\Chart;

class Users extends Base {
    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $timestamps = array_column($data, 'ts');
        $line1      = array_column($data, 'new_users_score_high');
        $line2      = array_column($data, 'new_users_score_med');
        $line3      = array_column($data, 'new_users_score_low');

        return $this->addEmptyDays([$timestamps, $line1, $line2, $line3]);
    }

    private function getFirstLine(int $apiKey): array {
        $dateRange = tirreno('utils')->dateRange->getDatesRangeFromRequest();
        if (!$dateRange) {
            $dateRange = [
                'endDate' => date('Y-m-d H:i:s'),
                'startDate' => date('Y-m-d H:i:s', 0),
            ];
        }
        $offset = tirreno('utils')->timezones->getCurrentOperatorOffset();
        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            ':resolution'   => tirreno('utils')->dateRange->getResolutionFromRequest(),
            ':offset'       => strval($offset),
            ':high_inf'     => tirreno('utils')->constants->USER_HIGH_SCORE_INF,
            //':high_sup'     => tirreno('utils')->constants->USER_HIGH_SCORE_SUP,
            ':med_inf'      => tirreno('utils')->constants->USER_MEDIUM_SCORE_INF,
            ':med_sup'      => tirreno('utils')->constants->USER_MEDIUM_SCORE_SUP,
            ':low_inf'      => tirreno('utils')->constants->USER_LOW_SCORE_INF,
            ':low_sup'      => tirreno('utils')->constants->USER_LOW_SCORE_SUP,
        ];

        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.created + :offset))::bigint              AS ts,
                COUNT(CASE WHEN event_account.score >= :high_inf                                   THEN TRUE END) AS new_users_score_high,
                COUNT(CASE WHEN event_account.score >= :med_inf AND event_account.score < :med_sup THEN TRUE END) AS new_users_score_med,
                COUNT(CASE WHEN event_account.score >= :low_inf AND event_account.score < :low_sup THEN TRUE END) AS new_users_score_low

            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.created >= :start_time AND
                event_account.created <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execQuery($query, $params);
    }
}
