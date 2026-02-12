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

class Timezones {
    public const FORMAT = 'Y-m-d H:i:s';
    public const EVENT_FORMAT = 'Y-m-d H:i:s.u';
    public const DEFAULT = 'UTC';

    private static function translateTimezone(array &$row, array $attributes = ['time', 'lastseen'], bool $useMilliseconds = false): void {
        foreach ($attributes as $attribute) {
            if (isset($row[$attribute])) {
                self::localizeForActiveOperator($row[$attribute], $useMilliseconds);
            }
        }
    }

    public static function translateTimezones(array &$rows, array $attributes = ['time', 'lastseen'], bool $useMilliseconds = false): void {
        $rows = array_map(function ($row) use ($attributes, $useMilliseconds) {
            self::translateTimezone($row, $attributes, $useMilliseconds);

            return $row;
        }, $rows);
    }

    public static function localizeTimestamp(string $time, \DateTimeZone $from, \DateTimeZone $until, bool $useMilliseconds): string {
        $format = ($useMilliseconds) ? self::EVENT_FORMAT : self::FORMAT;
        $time = ($useMilliseconds) ? $time : explode('.', $time)[0];

        $new = \DateTime::createFromFormat($format, $time, $from);
        $new->setTimezone($until);

        return $new->format($format);
    }

    public static function localizeForActiveOperator(string &$time, bool $useMilliseconds = false): void {
        $currentOperator = \Tirreno\Utils\Routes::getCurrentRequestOperator();
        $operatorTimezone = self::getTimezone($currentOperator?->timezone);
        $utc = self::getUtcTimezone();

        $time = self::localizeTimestamp($time, $utc, $operatorTimezone, $useMilliseconds);
    }

    public static function localizeTimestampsForActiveOperator(array $keys, array &$data): void {
        $currentOperator = \Tirreno\Utils\Routes::getCurrentRequestOperator();
        $operatorTimezone = self::getTimezone($currentOperator?->timezone);
        $utc = self::getUtcTimezone();

        $timestamps = array_intersect_key($data, array_flip($keys));

        foreach ($timestamps as $key => $t) {
            if ($t !== null) {
                $data[$key] = self::localizeTimestamp($t, $utc, $operatorTimezone, false);
            }
        }
    }

    public static function addOffset(string $time, int $offset, bool $useMilliseconds = false): string {
        $milliPart = null;
        if ($useMilliseconds) {
            $parts = explode('.', $time);
            if (count($parts) === 2) {
                $milliPart = $parts[1];
                $time = $parts[0];
            } else {
                $useMilliseconds = false;
            }
        }

        $time = date(self::FORMAT, (strtotime($time) + $offset));

        return $useMilliseconds ? $time . '.' . $milliPart : $time;
    }

    public static function localizeUnixTimestamps(array &$timestamps): void {
        $currentOperator = \Tirreno\Utils\Routes::getCurrentRequestOperator();
        $operatorTimezone = self::getTimezone($currentOperator?->timezone);
        $utcTime = new \DateTime('now', self::getUtcTimezone());
        $offsetInSeconds = $operatorTimezone->getOffset($utcTime);

        foreach (array_keys($timestamps) as $idx) {
            $timestamps[$idx] += $offsetInSeconds;
        }
    }

    public static function getOperatorOffset(?\Tirreno\Entities\Operator $operator): int {
        $operatorTimezone = self::getTimezone($operator?->timezone);
        $utcTime = new \DateTime('now', self::getUtcTimezone());

        return $operatorTimezone->getOffset($utcTime);
    }

    public static function getCurrentOperatorOffset(): int {
        return self::getOperatorOffset(\Tirreno\Utils\Routes::getCurrentRequestOperator());
    }

    public static function getServerOffset(): int {
        return (new \DateTime('now'))->getOffset();
    }

    public static function getTimezone(?string $timezone, string $default = self::DEFAULT): \DateTimeZone {
        return new \DateTimeZone(($timezone && in_array($timezone, \DateTimeZone::listIdentifiers())) ? $timezone : $default);
    }

    public static function getUtcTimezone(): \DateTimeZone {
        return new \DateTimeZone('UTC');
    }

    public static function getLastNDaysRange(int $days = 1, int $offset = 0): array {
        $now = time();
        $daySeconds = \Tirreno\Utils\Constants::get()->SECONDS_IN_DAY;

        $date = new \DateTime();
        $date->setTimestamp($now - ($daySeconds * $days) - (($now + $offset) % $daySeconds));

        $date->setTime(0, 0, 0);

        return [
            'endDate'   => date(self::FORMAT, $now),
            'startDate' => date(self::FORMAT, $date->getTimestamp() - $offset),
            'offset'    => $offset,
        ];
    }

    public static function getPreviousWeekRange(int $offset = 0): array {
        $now = time();

        $date = new \DateTime();
        $date->setTimestamp($now + $offset);
        $date->setTime(0, 0, 0);
        $dow = \Tirreno\Utils\Conversion::intValCheckEmpty($date->format('N'), 1);
        $day = \Tirreno\Utils\Constants::get()->SECONDS_IN_DAY;

        $weekStart = $date->getTimestamp() - $offset - ($dow - 1) * $day;

        return [
            'endDate'   => date(self::FORMAT, $weekStart - 1),
            'startDate' => date(self::FORMAT, $weekStart - 1 - 7 * $day),
            'offset'    => $offset,
        ];
    }

    public static function getCurDayRange(int $offset = 0): array {
        $now = time();

        $date = new \DateTime();
        $date->setTimestamp($now + $offset);

        $date->setTime(0, 0, 0);

        return [
            'endDate'   => date(self::FORMAT, $now),
            'startDate' => date(self::FORMAT, $date->getTimestamp() - $offset),
            'offset'    => $offset,
        ];
    }

    public static function getWeekAgoDayRange(int $offset = 0): array {
        $now = time();

        $date = new \DateTime();
        $date->setTimestamp($now + $offset);

        $date->setTime(0, 0, 0);

        $week = \Tirreno\Utils\Constants::get()->SECONDS_IN_WEEK;
        $day = \Tirreno\Utils\Constants::get()->SECONDS_IN_DAY;

        return [
            'endDate'   => date(self::FORMAT, $date->getTimestamp() - $offset - $week + $day),
            'startDate' => date(self::FORMAT, $date->getTimestamp() - $offset - $week),
            'offset'    => $offset,
        ];
    }

    public static function getCurWeekRange(int $offset = 0): array {
        $now = time();

        $date = new \DateTime();
        $date->setTimestamp($now + $offset);
        $date->setTime(0, 0, 0);
        $dow = \Tirreno\Utils\Conversion::intValCheckEmpty($date->format('N'), 0);
        $day = \Tirreno\Utils\Constants::get()->SECONDS_IN_DAY;

        return [
            'endDate'   => date(self::FORMAT, $now),
            'startDate' => date(self::FORMAT, $date->getTimestamp() - $offset - (($dow - 1) * $day)),
            'offset'    => $offset,
        ];
    }

    public static function timezonesList(): array {
        $utcTime = new \DateTime('now', self::getUtcTimezone());
        $timezones = \Tirreno\Utils\Variables::getAvailableTimezones();

        foreach ($timezones as $key => $value) {
            $offset = (new \DateTimeZone($key))->getOffset($utcTime);
            $part = ($offset < 0) ? '-' . date('H:i', -$offset) : '+' . date('H:i', $offset);
            $timezones[$key] = explode('(', $value)[0] . '(UTC' . $part . ')';
        }

        return $timezones;
    }
}
