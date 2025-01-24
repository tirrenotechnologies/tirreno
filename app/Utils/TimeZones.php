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
}
