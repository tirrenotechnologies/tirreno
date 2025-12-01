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

namespace Sensor\Model\Validated;

class Base {
    private string $type;
    public bool $invalid;
    public string $origin;

    public function __construct(string $value, string $type) {
        $this->origin = $value;
        $this->type = $type;
    }

    public function validationStatement(): ?string {
        if ($this->invalid) {
            return "$this->type validation error on `$this->origin`";
        }

        return null;
    }
}
