<?php

/**
 * tirreno ~ open security analytics
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

namespace Utils;

class ElapsedDate {
    //https://gist.github.com/fazlurr/473a46d6d2e967119e77b5339dd10bc2
    public static function short(?string $dt): ?string {
        return $dt ? date('d/m/Y H:i:s', strtotime($dt)) : null;
    }

    public static function date(?string $dt): ?string {
        return $dt ? date('d/m/Y', strtotime($dt)) : null;
    }

    public static function long(string $dt): string {
        $ret = [];
        $secs = strtotime($dt);

        $secs = time() - $secs;

        $bit = [
            ' year' => $secs / 31556926 % 12,
            ' week' => $secs / 604800 % 52,
            ' day' => $secs / 86400 % 7,
            ' hour' => $secs / 3600 % 24,
            ' minute' => $secs / 60 % 60,
            ' second' => $secs % 60,
        ];

        foreach ($bit as $k => $v) {
            if ($v > 1) {
                $ret[] = $v . $k . 's';
            }
            if ($v === 1) {
                $ret[] = $v . $k;
            }
        }

        array_splice($ret, count($ret) - 1, 0, 'and');
        $ret[] = 'ago.';

        return join(' ', $ret);
    }
}
