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

class SessionStat extends Base {
    protected $DB_TABLE_NAME = 'event_session';

    public function getData(int $apiKey): array {
        $itemsByDate = [];
        $items = $this->getCounts($apiKey);

        foreach ($items as $item) {
            $itemsByDate[$item['ts']] = [
                $item['new_device_count_sum'],
                $item['new_ip_count_sum'],
                $item['event_session_cnt'],
                $item['event_count_max'],
            ];
        }

        $request = $this->f3->get('REQUEST');
        // use offset shift because $startTs/$endTs compared with shifted ['ts']
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();
        $datesRange = $this->getLatestNDatesRange(14, $offset);
        $endTs = strtotime($datesRange['endDate']);
        $startTs = strtotime($datesRange['startDate']);
        $step = \Utils\Constants::get('CHART_RESOLUTION')[$this->getResolution($request)];

        $endTs = $endTs - ($endTs % $step);
        $startTs = $startTs - ($startTs % $step);

        $midTs = $startTs + $step * 7;

        foreach ($itemsByDate as $ts => $value) {
            if ($ts <= $midTs) {
                $newTs = $ts + $step * 7;
                if (!array_key_exists($newTs, $itemsByDate)) {
                    $itemsByDate[$newTs] = [
                        0, 0, 0, 0,
                        $value[0], $value[1], $value[2], $value[3],
                    ];
                } else {
                    $itemsByDate[$newTs][] = $value[0];
                    $itemsByDate[$newTs][] = $value[1];
                    $itemsByDate[$newTs][] = $value[2];
                    $itemsByDate[$newTs][] = $value[3];
                }
            }
        }

        while ($endTs >= $startTs) {
            if (!isset($itemsByDate[$startTs]) && $startTs > $midTs) {
                $itemsByDate[$startTs] = [0, 0, 0, 0, 0, 0, 0, 0];
            } elseif (isset($itemsByDate[$startTs]) && count($itemsByDate[$startTs]) === 4) {
                $itemsByDate[$startTs][] = 0;
                $itemsByDate[$startTs][] = 0;
                $itemsByDate[$startTs][] = 0;
                $itemsByDate[$startTs][] = 0;
            }

            $startTs += $step;
        }

        foreach (array_keys($itemsByDate) as $key) {
            if ($key <= $midTs) {
                unset($itemsByDate[$key]);
            }
        }

        ksort($itemsByDate);

        $result = [array_keys($itemsByDate)];

        for ($i = 0; $i < 8; ++$i) {
            $result[] = array_column($itemsByDate, $i);
        }

        return $result;
    }

    protected function executeOnRangeById(string $query, int $apiKey): array {
        $request = $this->f3->get('REQUEST');
        // do not use offset because :start_time/:end_time compared with UTC event.time
        $dateRange = $this->getLatestNDatesRange(14);
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();

        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            //':resolution'   => $this->getResolution($request),
            ':resolution'   => 60 * 60 * 24,
            ':id'           => $request['id'],
            ':offset'       => strval($offset),     // str for postgres
        ];

        return $this->execQuery($query, $params);
    }

    private function getCounts(int $apiKey): array {
        $query = (
            'SELECT
                ((EXTRACT(EPOCH FROM event_session.created)::bigint + :offset::bigint) / :resolution) * :resolution as ts,

                COUNT(event_session.id)                             AS event_session_cnt,
                MAX(event_session_stat.event_count)                 AS event_count_max,
                FLOOR(AVG(event_session_stat.event_count))::int     AS event_count_avg,
                MAX(event_session_stat.device_count)                AS device_count_max,
                FLOOR(AVG(event_session_stat.device_count))::int    AS device_count_avg,
                MAX(event_session_stat.ip_count)                    AS ip_count_max,
                FLOOR(AVG(event_session_stat.ip_count))::int        AS ip_count_avg,
                MAX(event_session_stat.country_count)               AS country_count_max,
                FLOOR(AVG(event_session_stat.country_count))::int   AS country_count_avg,
                SUM(event_session_stat.new_ip_count)                AS new_ip_count_sum,
                SUM(event_session_stat.new_device_count)            AS new_device_count_sum,
                jsonb_agg(event_session_stat.event_types)           AS event_types,
                jsonb_agg(event_session_stat.http_codes)            AS http_codes

            FROM
                event_session

            LEFT JOIN event_session_stat
            ON event_session.id = event_session_stat.session_id

            WHERE
                event_session.account_id = :id AND
                event_session.key = :api_key AND
                event_session.created >= :start_time AND
                event_session.created <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->executeOnRangeById($query, $apiKey);
    }
}
