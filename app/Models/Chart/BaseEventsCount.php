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

class BaseEventsCount extends \Models\BaseSql {
    use \Traits\DateRange;

    protected $DB_TABLE_NAME = 'event';

    public function getData(int $apiKey): array {
        $itemsByDate = [];
        $items = $this->getCounts($apiKey);

        foreach ($items as $item) {
            $itemsByDate[$item['ts']] = $item['event_count'];
        }
        $request = $this->f3->get('REQUEST');
        // use offset shift because $startTs/$endTs compared with shifted ['ts']
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();
        $datesRange = $this->getLatest180DatesRange($offset);
        $endTs = strtotime($datesRange['endDate']);
        $startTs = strtotime($datesRange['startDate']);
        $step = \Utils\Constants::get('CHART_RESOLUTION')[$this->getResolution($request)];

        $endTs = $endTs - ($endTs % $step);
        $startTs = $startTs - ($startTs % $step);

        while ($endTs >= $startTs) {
            if (!isset($itemsByDate[$startTs])) {
                $itemsByDate[$startTs] = null;
            }

            $startTs += $step;
        }

        ksort($itemsByDate);

        $ox = [];
        $data = [];

        foreach ($itemsByDate as $key => $value) {
            $ox[] = $key;
            $data[] = $value;
        }

        return [$ox, $data];
    }

    protected function executeOnRangeById(string $query, int $apiKey): array {
        $request = $this->f3->get('REQUEST');
        // do not use offset because :start_time/:end_time compared with UTC event.time
        $dateRange = $this->getLatest180DatesRange();
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();

        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            ':resolution'   => $this->getResolution($request),
            ':id'           => $request['id'],
            ':offset'       => strval($offset),     // str for postgres
        ];

        return $this->execQuery($query, $params);
    }
}
