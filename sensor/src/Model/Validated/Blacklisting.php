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

class Blacklisting extends Base {
    private const INVALIDPLACEHOLDER = false;
    public bool $value;

    public function __construct(string $value) {
        parent::__construct($value, 'blacklisting');

        $invalid = false;
        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($value === null) {
            $invalid = true;
            $value = self::INVALIDPLACEHOLDER;
        }

        $this->value = $value;
        $this->invalid = $invalid;
    }
}
