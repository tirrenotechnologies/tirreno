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

namespace Tirreno\Utils;

class DateRange {
    public static function isQueueTimeouted(string $updated): bool {
        return !self::inIntervalTillNow($updated, tirreno('utils')->constants->ACCOUNT_OPERATION_QUEUE_AUTO_UNCLOG_AFTER_SEC);
    }

    public static function getDatesRangeByGivenDates(string $startDate, string $endDate, int $offset): array {
        return static::getDatesRangeByUnixDates(strtotime($startDate), strtotime($endDate), $offset);
    }

    public static function getDatesRangeByUnixDates(int $startDate, int $endDate, int $offset): array {
        return [
            'endDate'   => date('Y-m-d H:i:s', $endDate + $offset),
            'startDate' => date('Y-m-d H:i:s', $startDate + $offset),
        ];
    }

    public static function getDateString(int $ts): string {
        return date('Y-m-d H:i:s', $ts);
    }

    public static function getDatesRangeFromRequest(int $offset = 0): ?array {
        $dates      = null;
        $dateTo     = tirreno('utils')->conversion->getTimestampRequestParam('dateTo', true);
        $dateFrom   = tirreno('utils')->conversion->getTimestampRequestParam('dateFrom', true);
        $keepDates  = tirreno('utils')->conversion->getIntRequestParam('keepDates', true);

        if ($dateTo && $dateFrom) {
            $dates = self::getDatesRangeByGivenDates($dateFrom, $dateTo, $offset);

            $endDate = $keepDates ? $dates['endDate'] : null;
            $startDate = $keepDates ? $dates['startDate'] : null;

            tirreno('session')->set('filterEndDate', $endDate);
            tirreno('session')->set('filterStartDate', $startDate);
        }

        return $dates;
    }

    public static function getLatestNDatesRangeFromRequest(int $days, int $offset = 0): array {
        $day = tirreno('utils')->constants->SECONDS_IN_DAY;

        return [
            'endDate'   => date('Y-m-d 23:59:59', time() + $offset),
            'startDate' => date('Y-m-d 00:00:01', time() - ($days * $day) + $offset),
        ];
    }

    public static function getResolutionFromRequest(): string {
        $resolution = tirreno('utils')->conversion->getStringRequestParam('resolution', true) ?? 'day';

        return array_key_exists($resolution, tirreno('utils')->constants->CHART_RESOLUTION) ? $resolution : 'day';
    }

    public static function inIntervalTillNow(?string $time, int $interval): ?bool {
        if (!$time) {
            return null;
        }

        $dt1 = new \DateTime(gmdate('Y-m-d H:i:s'));
        $dt2 = new \DateTime($time);

        return $interval > abs($dt1->getTimestamp() - $dt2->getTimestamp());
    }
}
