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

namespace Tirreno\Entities;

class Rules {
    public array $rules;
    public int $key;

    private array $tsFields = ['updated', 'created', 'proportionUpdated'];

    public function __construct(
        array $rules,
        int $key,
    ) {
        $this->rules    = $rules;
        $this->key      = $key;
    }

    public static function getAll(int $key): ?self {
        $result = tirreno('models')->rules->getRulesByOperator($key);

        $preparedRules = [];

        foreach ($result as $rule) {
            $preparedRules[] = self::fillEntity($rule, $key);
        }

        return new self($preparedRules, $key);
    }

    public static function getByUserId(int $userId, int $key): ?self {
        $result = tirreno('models')->rules->getRulesByUserId($userId, $key);

        $preparedRules = [];

        foreach ($result as $rule) {
            $preparedRules[] = self::fillEntity($rule, $key);
        }

        return new self($preparedRules, $key);
    }

    private static function fillEntity(array $data, int $key): \Tirreno\Entities\Rule {
        return tirreno('entities')->rule->getFromQuery($data, $key);
    }

    public function localizeTimestamps(?string $timezone = null): void {
        $timezone = tirreno('utils')->timezones->getTimezone($timezone ?? tirreno('session')->getCurrentOperator()?->timezone);
        $utc = tirreno('utils')->timezones->getUtcTimezone();

        foreach ($this->rules as &$rule) {
            foreach ($this->tsFields as $prop) {
                $rule[$prop] = tirreno('utils')->timezones->localizeTimestamp($rule[$prop], $utc, $timezone, false);
            }
        }

        unset($rule);
    }
}
