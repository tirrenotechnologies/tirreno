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

declare(strict_types=1);

namespace Sensor\Model\Validated\Payloads;

class Base extends \Sensor\Model\Validated\Base {
    protected array $optionalFields;
    protected array $requiredFields;

    protected bool $set;
    protected bool $dump;

    public array|string|null $value;

    public function __construct(mixed $value) {
        parent::__construct(json_encode($value), 'payload');

        if (!is_array($value)) {
            $this->invalid = true;
            $this->value = null;

            return;
        }

        $this->invalid = false;
        $data = [];

        if (!$this->set) {
            $data = $this->preserveItem($value);
        } else {
            foreach ($value as $payload) {
                $item = [];
                if ($payload && is_array($payload)) {
                    $item = $this->preserveItem($payload);

                    if ($item && count($item)) {
                        $data[] = $item;
                    } else {
                        $this->invalid = true;
                    }
                } else {
                    $this->invalid = true;
                }
            }
        }

        if (!count($data)) {
            $this->invalid = true;
        }

        $this->value = count($data) ? ($this->dump ? json_encode($data) : $data) : null;
    }

    private function preserveItem(array $item): ?array {
        $data = [];

        foreach ($this->requiredFields as $key) {
            if (isset($item[$key])) {
                $data[$key] = $this->convert($item[$key]);
            } else {
                $data[$key] = null;
                $this->invalid = true;
            }
        }

        foreach ($this->optionalFields as $key) {
            $data[$key] = isset($item[$key]) ? $this->convert($item[$key]) : null;
        }

        return count($data) ? $data : null;
    }

    private function convert(mixed $val): ?string {
        if (is_array($val)) {
            return json_encode($val);
        }

        if (is_int($val) || is_float($val) || is_string($val) || is_bool($val)) {
            return strval($val);
        }

        return null;
    }
}
