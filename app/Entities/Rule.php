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

class Rule {
    public ?int $id;
    public ?int $value;
    public ?string $created;
    public ?float $proportion;
    public ?string $proportionUpdated;
    public string $uid;
    public string $name;
    public string $descr;
    public array $attributes;
    public string $updated;
    public bool $validated;
    public ?bool $missing;
    public int $key;

    private array $tsFields = ['updated', 'created', 'proportionUpdated'];

    public function __construct(
        ?int $id,
        ?int $value,
        ?string $created,
        ?float $proportion,
        ?string $proportionUpdated,
        string $uid,
        string $name,
        string $descr,
        string $attributes,
        string $updated,
        bool $validated,
        ?bool $missing,
        int $key,
    ) {
        $this->id                   = $id;
        $this->value                = $value;
        $this->created              = $created;
        $this->proportion           = $proportion;
        $this->proportionUpdated    = $proportionUpdated;
        $this->uid                  = $uid;
        $this->name                 = $name;
        $this->descr                = $descr;
        $this->attributes           = json_decode($attributes, true);
        $this->updated              = $updated;
        $this->validated            = $validated;
        $this->missing              = $missing;
        $this->key                  = $key;
    }

    // not operator rule id -- uid!
    public static function getById(string $uid, int $key): ?self {
        $rule = tirreno('models')->operatorsRules->getRuleWithOperatorValue($uid, tirreno('utils')->constants->PRIMARY_RULES_SET_ID, $key);

        return self::getFromQuery($rule, $key);
    }

    public static function getFromQuery(array $data, int $key): self {
        return new \Tirreno\Entities\Rule(
            $data['id'],
            $data['value'],
            $data['created_at'],
            $data['proportion'],
            $data['proportion_updated_at'],
            $data['uid'],
            $data['name'],
            $data['descr'],
            $data['attributes'],
            $data['updated'],
            $data['validated'],
            $data['missing'],
            $key,
        );
    }

    public function localizeTimestamps(?string $timezone = null): void {
        $timezone = tirreno('utils')->timezones->getTimezone($timezone ?? tirreno('session')->getCurrentOperator()?->timezone);
        $utc = tirreno('utils')->timezones->getUtcTimezone();

        foreach ($this->tsFields as $prop) {
            if (property_exists($this, $prop) && $this->$prop) {
                $this->$prop = tirreno('utils')->timezones->localizeTimestamp($this->$prop, $utc, $timezone, false);
            }
        }
    }
}
