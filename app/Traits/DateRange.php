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

namespace Traits;

trait DateRange {
    public function getDatesRangeByGivenDates(string $startDate, string $endDate, int $offset): array {
        return [
            'endDate' => date('Y-m-d H:i:s', strtotime($endDate) + $offset),
            'startDate' => date('Y-m-d H:i:s', strtotime($startDate) + $offset),
        ];
    }

    public function getDatesRange(array $request, int $offset = 0): ?array {
        $dates = null;
        $dateTo = $request['dateTo'] ?? null;
        $dateFrom = $request['dateFrom'] ?? null;
        $keepDates = $request['keepDates'] ?? null;

        if ($dateTo && $dateFrom) {
            $dates = $this->getDatesRangeByGivenDates($dateFrom, $dateTo, $offset);

            $endDate = null;
            $startDate = null;

            if ($keepDates) {
                $endDate = $dates['endDate'];
                $startDate = $dates['startDate'];
            }

            $this->f3->set('SESSION.filterEndDate', $endDate);
            $this->f3->set('SESSION.filterStartDate', $startDate);
        }

        return $dates;
    }

    public function getLatest180DatesRange(int $offset = 0): array {
        return [
            'endDate' => date('Y-m-d 23:59:59', time() + $offset),
            'startDate' => date('Y-m-d 00:00:01', time() - (180 * 24 * 60 * 60) + $offset),
        ];
    }

    public function getResolution(array $request): string {
        $resolution = $request['resolution'] ?? 'day';

        return array_key_exists($resolution, \Utils\Constants::CHART_RESOLUTION) ? $resolution : 'day';
    }
}
