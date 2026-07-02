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

class Sort {
    public static function cmpTimestamp(array $left, array $right): int {
        return $left['ts'] - $right['ts'];
    }

    public static function cmpScore(array $left, array $right): int {
        return $right['score'] <=> $left['score'];
    }

    public static function cmpSetScore(array $left, array $right): int {
        return $right['set'] <=> $left['set'] ?: $right['score'] <=> $left['score'];
    }

    public static function cmpSetUid(array $left, array $right): int {
        return $right['set'] <=> $left['set'] ?: $right['uid'] <=> $left['uid'];
    }

    public static function cmpRule(array $left, array $right): int {
        if ($left['validated'] !== $right['validated']) {
            return ($right['validated'] <=> $left['validated']);
        }

        if (($left['missing'] === true) !== ($right['missing'] === true)) {
            return (tirreno('utils')->conversion->intVal($left['missing']) <=> tirreno('utils')->conversion->intVal($right['missing']));
        }

        return $left['uid'] <=> $right['uid'];
    }
}
