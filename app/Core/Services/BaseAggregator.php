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

namespace Tirreno\Core\Services;

abstract class BaseAggregator {
    protected array $objects;
    protected array $objectsMap;

    protected array $overrides;
    protected string $namespace;

    protected array $objectsReverseMap = [];

    protected bool $singletons = true;

    public function getByClassName(string $name): ?object {
        if (!$this->objectsReverseMap) {
            $this->objectsReverseMap = array_flip($this->objectsMap);
        }

        $value = $this->objectsReverseMap[$name] ?? null;

        if (!$value) {
            return null;
        }

        return $this->$value;
    }

    public function __get(string $name): mixed {
        if ($this->singletons) {
            $obj = $this->objects[$name] ?? null;

            if ($obj) {
                return $obj;
            }
        }

        $className = $this->overrides[$name] ?? null;
        if ($className) {
            $this->objects[$name] = $this->createObject($name, $className, false);

            return $this->objects[$name];
        }

        $className = $this->objectsMap[$name] ?? null;
        if ($className) {
            $this->objects[$name] = $this->createObject($name, $className, true);

            return $this->objects[$name];
        }

        throw new \Exception('Unknown property ' . $name);
    }

    public function __set(string $name, mixed $value): void {
        $this->overrides[$name] = $value;
    }

    public function override(string $name, mixed $value): void {
        $this->overrides[$name] = $value;
        $this->objects[$name] = $this->createObject($name, $value, false);
    }

    protected function createObject(string $name, string $className, bool $getFullClass): object {
        return new ($this->getClassName($className, $getFullClass))();
    }

    protected function getClassName(string $className, bool $getFullClass): string {
        return $getFullClass ? sprintf($this->namespace, $className) : $className;
    }
}
