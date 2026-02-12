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

class Conversion {
    public static function intVal(mixed $value, ?int $default = null): ?int {
        if (is_string($value) && $value !== '') {
            $value = ltrim($value, '0');
            if ($value === '') {
                $value = '0';
            }
        }

        $validated = filter_var($value, FILTER_VALIDATE_INT);

        return $validated !== false ? $validated : (is_float($value) || is_bool($value) ? intval($value) : $default);
    }

    public static function intValCheckEmpty(mixed $value, ?int $default = null): ?int {
        return $value ? self::intVal($value, $default) : $default;
    }

    public static function getIntRequestParam(string $key, bool $nullable = false): ?int {
        return self::intVal(\Base::instance()->get('REQUEST.' . $key), $nullable ? null : 0);
    }

    public static function getStringRequestParam(string $key, bool $nullable = false): ?string {
        $value = \Base::instance()->get('REQUEST.' . $key);

        return $value ? strval($value) : ($nullable ? null : '');
    }

    public static function getArrayRequestParam(string $key, bool $nullable = false): ?array {
        $value = \Base::instance()->get('REQUEST.' . $key);

        return is_array($value) ? array_values($value) : ($nullable ? null : []);
    }

    public static function getDictionaryRequestParam(string $key, bool $nullable = false): ?array {
        $value = \Base::instance()->get('REQUEST.' . $key);

        return is_array($value) ? $value : ($nullable ? null : []);
    }

    public static function getIntUrlParam(string $key, bool $nullable = false): ?int {
        return self::intVal(\Base::instance()->get('PARAMS.' . $key), $nullable ? null : 0);
    }

    public static function formatKiloValue(int $value): string {
        if ($value >= 1000000) {
            return strval(floor($value / 1000000)) . 'M';
        }

        if ($value >= 1000) {
            return strval(floor($value / 1000)) . 'k';
        }

        return strval($value);
    }

    public static function filterIp(mixed $var): string|false {
        return filter_var($var, FILTER_VALIDATE_IP);
    }

    public static function filterIpGetType(mixed $var): int|false {
        if (filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return 4;
        } elseif (filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return 6;
        }

        return false;
    }

    public static function filterEmail(mixed $var): string|false {
        return filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    public static function filterBool(mixed $var): ?bool {
        return filter_var($var, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
