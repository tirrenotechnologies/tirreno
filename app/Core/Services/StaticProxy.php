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

class StaticProxy {
    protected string $class;

    public function __construct(string $class) {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Class not found: {$class}");
        }

        $this->class = $class;
    }

    public function __get(string $name): mixed {
        if ($name === 'class') {
            return $this->class;
        }
        //return $this->class::$name;
        $const = $this->class . '::' . $name;

        if (!defined($const)) {
            throw new \OutOfBoundsException("Constant {$const} not found");
        }

        return constant($const);
    }

    public function __call(string $method, array $args): mixed {
        if (!method_exists($this->class, $method)) {
            throw new \BadMethodCallException("Method {$this->class}::{$method}() not found");
        }

        //return $this->class::$method(...$args);
        return call_user_func_array([$this->class, $method], $args);
    }
}
