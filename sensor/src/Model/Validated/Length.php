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

class Length extends Base {
    public string $value;

    public function __construct(string $value, string $type, int $limit = 100) {
        parent::__construct($value, $type);

        if (strlen($value) > $limit) {
            $this->value = substr($value, 0, $limit);
        } else {
            // even if empty string!
            $this->value = $value;
        }

        $this->invalid = strlen($value) > $limit;
    }

    public function isEmpty(): bool {
        return $this->value === '';
    }
}
