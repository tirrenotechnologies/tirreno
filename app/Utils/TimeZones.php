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

namespace Utils;

class TimeZones {
    public const FORMAT = 'Y-m-d H:i:s';
    public const EVENT_FORMAT = 'Y-m-d H:i:s.u';
    public const DEFAULT = 'UTC';

    public static function localizeTimeStamp(string $time, \DateTimeZone $from, \DateTimeZone $to, bool $useMilliseconds): string {
        $format = ($useMilliseconds) ? self::EVENT_FORMAT : self::FORMAT;
        $time = ($useMilliseconds) ? $time : explode('.', $time)[0];

        $new = \DateTime::createFromFormat($format, $time, $from);
        $new->setTimezone($to);

        return $new->format($format);
    }

    public static function localizeForActiveOperator(string &$time, bool $useMilliseconds = false): void {
        $f3 = \Base::instance();
        $currentOperator = $f3->get('CURRENT_USER');
        $operatorTimeZone = new \DateTimeZone($currentOperator->timezone ?? self::DEFAULT);
        $utc = new \DateTimeZone(self::DEFAULT);
        $time = self::localizeTimeStamp($time, $utc, $operatorTimeZone, $useMilliseconds);
    }

    public static function localizeTimestampsForActiveOperator(array $keys, array &$data): void {
        $f3 = \Base::instance();
        $currentOperator = $f3->get('CURRENT_USER');
        $operatorTimeZone = new \DateTimeZone($currentOperator->timezone ?? self::DEFAULT);
        $utc = new \DateTimeZone(self::DEFAULT);

        $ts = array_intersect_key($data, array_flip($keys));

        foreach ($ts as $key => $t) {
            if ($t !== null) {
                $data[$key] = self::localizeTimeStamp($t, $utc, $operatorTimeZone, false);
            }
        }
    }

    public static function localizeUnixTimestamps(array &$ts): void {
        $f3 = \Base::instance();
        $currentOperator = $f3->get('CURRENT_USER');
        $operatorTimeZone = new \DateTimeZone($currentOperator->timezone ?? self::DEFAULT);
        $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));
        $offsetInSeconds = $operatorTimeZone->getOffset($utcTime);

        foreach ($ts as $i => $t) {
            $ts[$i] += $offsetInSeconds;
        }
    }

    public static function getCurrentOperatorOffset(): int {
        $f3 = \Base::instance();
        $currentOperator = $f3->get('CURRENT_USER');
        $operatorTimeZone = new \DateTimeZone($currentOperator->timezone ?? self::DEFAULT);
        $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));

        return $operatorTimeZone->getOffset($utcTime);
    }

    public static function getLastNDaysRange(int $days = 1, int $offset = 0): array {
        $now = time();
        $daySeconds = 24 * 60 * 60;

        return [
            'endDate'   => date(self::FORMAT, $now),
            'startDate' => date(self::FORMAT, $now - ($daySeconds * $days) - (($now + $offset) % $daySeconds)),
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

    public static function getCurWeekRange(int $offset = 0): array {
        $now = time();

        $date = new \DateTime();
        $date->setTimestamp($now + $offset);

        $date->setTime(0, 0, 0);

        $dow = (int) $date->format('N');

        return [
            'endDate'   => date(self::FORMAT, $now),
            'startDate' => date(self::FORMAT, $date->getTimestamp() - $offset - (($dow - 1) * 24 * 60 * 60)),
            'offset'    => $offset,
        ];
    }

    public static function getCurMonthRange(int $offset = 0): array {
        $now = time();

        $date = new \DateTime();
        $date->setTimestamp($now + $offset);

        $date->setTime(0, 0, 0);

        $day = (int) $date->format('j');

        return [
            'endDate'   => date(self::FORMAT, $now),
            'startDate' => date(self::FORMAT, $date->getTimestamp() - $offset - (($day - 1) * 24 * 60 * 60)),
            'offset'    => $offset,
        ];
    }

    public static function timeZonesList(): array {
        $timezones = (\Base::instance())->get('timezones');
        $utcTime = new \DateTime('now', new \DateTimeZone('UTC'));

        $st = microtime(true);

        foreach ($timezones as $key => $value) {
            $offset = (new \DateTimeZone($key))->getOffset($utcTime);
            $part = ($offset < 0) ? '-' . date('H:i', -$offset) : '+' . date('H:i', $offset);
            $timezones[$key] = explode('(', $value)[0] . '(UTC' . $part . ')';
        }

        return $timezones;
    }
}
