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

abstract class Base extends \Tirreno\Models\Base {
    protected function concatDataLines(array $data1, string $field1, array $data2, string $field2, array $data3 = [], ?string $field3 = null): array {
        $data0 = [];
        $iters = count($data1);

        for ($i = 0; $i < $iters; ++$i) {
            $item = $data1[$i];
            $ts = $item['ts'];

            $data0[$ts] = [
                'ts'    => $ts,
                $field1 => $item[$field1],
                $field2 => 0,
            ];

            if ($field3) {
                $data0[$ts][$field3] = 0;
            }
        }

        $iters = count($data2);

        for ($i = 0; $i < $iters; ++$i) {
            $item = $data2[$i];
            $ts = $item['ts'];

            if (!array_key_exists($ts, $data0)) {
                $data0[$ts] = [
                    'ts'    => $ts,
                    $field1 => 0,
                    $field2 => 0,
                ];

                if ($field3) {
                    $data0[$ts][$field3] = 0;
                }
            }

            $data0[$ts][$field2] = $item[$field2];
        }

        $iters = count($data3);

        for ($i = 0; $i < $iters; ++$i) {
            $item = $data3[$i];
            $ts = $item['ts'];

            if (!array_key_exists($ts, $data0)) {
                $data0[$ts] = [
                    'ts'    => $ts,
                    $field1 => 0,
                    $field2 => 0,
                    $field3 => 0,
                ];
            }

            $data0[$ts][$field3] = $item[$field3];
        }

        // TODO: tmp order troubles fix
        usort($data0, [\Tirreno\Utils\Sort::class, 'cmpTimestamp']);

        return $data0;
    }

    protected function addEmptyDays(array $params): array {
        $cnt = count($params);
        $data = array_fill(0, $cnt, []);

        $step = tirreno('utils')->constants->CHART_RESOLUTION[tirreno('utils')->dateRange->getResolutionFromRequest()];
        // use offset shift because $startTs/$endTs compared with shifted ['ts']
        $offset = tirreno('utils')->timezones->getCurrentOperatorOffset();
        $dateRange = tirreno('utils')->dateRange->getDatesRangeFromRequest($offset);

        if (!$dateRange) {
            $now = time() + $offset;
            $week = tirreno('utils')->constants->SECONDS_IN_WEEK;
            if (count($params[0]) === 0) {
                $dateRange = [
                    'endDate' => date('Y-m-d H:i:s', $now),
                    'startDate' => date('Y-m-d 00:00:01', $now - $week),
                ];
            } else {
                $firstTs = ($now - $params[0][0] < $week) ? $now - $week : $params[0][0];
                $dateRange = [
                    'endDate'   => date('Y-m-d H:i:s', $now),
                    'startDate' => date('Y-m-d 00:00:01', $firstTs),
                ];
            }
        }

        $endTs = strtotime($dateRange['endDate']);
        $startTs = strtotime($dateRange['startDate']);

        $endTs = $endTs - ($endTs % $step);
        $startTs = $startTs - ($startTs % $step);

        $timestamps = $params[0];

        while ($endTs >= $startTs) {
            $itemIdx = array_search($startTs, $timestamps);

            $data[0][] = $startTs;

            for ($i = 1; $i < $cnt; ++$i) {
                $data[$i][] = ($itemIdx !== false) ? $params[$i][$itemIdx] : 0;
            }

            $startTs += $step;
        }

        return $data;
    }

    protected function execute(string $query, int $apiKey): array {
        // do not use offset because :start_time/:end_time compared with UTC db timestamps
        $dateRange = tirreno('utils')->dateRange->getDatesRangeFromRequest();

        // Search request does not contain daterange param
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
            ':offset'       => strval($offset),     // str for postgres
        ];

        return $this->execQuery($query, $params);
    }
}
