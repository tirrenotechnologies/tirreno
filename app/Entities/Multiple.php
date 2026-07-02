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

abstract class Multiple {
    public array $data;
    public int $key;

    protected static string $singleEntity;

    final public function __construct(
        array $data,
        int $key,
    ) {
        $this->data  = $data;
        $this->key  = $key;
    }

    public static function buildFromArray(array $data, int $key): static {
        $prepared = [];

        foreach ($data as $record) {
            $prepared[] = self::fillEntity($record, $key);
        }

        return new static($prepared, $key);
    }

    private static function fillEntity(array $data, int $key): object {
        $property = static::$singleEntity;

        return tirreno('entities')->$property->getFromQuery($data, $key);
    }

    public function localizeTimestamps(?string $timezone = null): void {
        foreach ($this->data as &$entity) {
            $entity->localizeTimestamps($timezone);
        }

        unset($entity);
    }
}
