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

abstract class Base extends \Models\BaseSql {
    use \Traits\DateRange;

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
        usort($data0, function ($a, $b) {
            return $a['ts'] - $b['ts'];
        });

        return $data0;
    }

    protected function addEmptyDays(array $params): array {
        $cnt = count($params);
        $data = array_fill(0, $cnt, []);

        $request = $this->f3->get('REQUEST');
        $step = \Utils\Constants::get('CHART_RESOLUTION')[$this->getResolution($request)];
        // use offset shift because $startTs/$endTs compared with shifted ['ts']
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();
        $dateRange = $this->getDatesRange($request, $offset);

        if (!$dateRange) {
            $now = time() + $offset;
            $week = 7 * 24 * 60 * 60;
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

        $ox = $params[0];

        while ($endTs >= $startTs) {
            $itemIdx = array_search($startTs, $ox);

            $data[0][] = $startTs;

            for ($i = 1; $i < $cnt; ++$i) {
                $data[$i][] = ($itemIdx !== false) ? $params[$i][$itemIdx] : 0;
            }

            $startTs += $step;
        }

        return $data;
    }

    protected function execute(string $query, int $apiKey): array {
        $request = $this->f3->get('REQUEST');

        // do not use offset because :start_time/:end_time compared with UTC db timestamps
        $dateRange = $this->getDatesRange($request);

        // Search request does not contain daterange param
        if (!$dateRange) {
            $dateRange = [
                'endDate' => date('Y-m-d H:i:s'),
                'startDate' => date('Y-m-d H:i:s', 0),
            ];
        }

        $offset = \Utils\TimeZones::getCurrentOperatorOffset();

        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            ':resolution'   => $this->getResolution($request),
            ':offset'       => strval($offset),     // str for postgres
        ];

        return $this->execQuery($query, $params);
    }
}
