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

    protected $alertTypesParams;
    protected $editTypesParams;
    protected $normalTypesParams;

    protected $alertFlatIds;
    protected $editFlatIds;
    protected $normalFlatIds;

    public function __construct() {
        parent::__construct();

        [$this->alertTypesParams, $this->alertFlatIds]      = $this->getArrayPlaceholders(\Utils\Constants::ALERT_EVENT_TYPES, 'alert');
        [$this->editTypesParams, $this->editFlatIds]        = $this->getArrayPlaceholders(\Utils\Constants::EDITING_EVENT_TYPES, 'edit');
        [$this->normalTypesParams, $this->normalFlatIds]    = $this->getArrayPlaceholders(\Utils\Constants::NORMAL_EVENT_TYPES, 'normal');
    }

    public function getData(int $apiKey): array {
        $itemsByDate = [];
        $items = $this->getCounts($apiKey);

        foreach ($items as $item) {
            $itemsByDate[$item['ts']] = [
                $item['event_normal_type_count'],
                $item['event_editing_type_count'],
                $item['event_alert_type_count'],
            ];
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
                $itemsByDate[$startTs] = [null, null, null];
            }

            $startTs += $step;
        }

        ksort($itemsByDate);

        $ox = [];
        $l1 = [];
        $l2 = [];
        $l3 = [];

        foreach ($itemsByDate as $key => $value) {
            $ox[] = $key;
            $l1[] = $value[0];
            $l2[] = $value[1];
            $l3[] = $value[2];
        }

        return [$ox, $l1, $l2, $l3];
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

        $params = array_merge($params, $this->alertTypesParams);
        $params = array_merge($params, $this->editTypesParams);
        $params = array_merge($params, $this->normalTypesParams);

        return $this->execQuery($query, $params);
    }
}
