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

class Email extends Length {
    private const PLACEHOLDER = 'unknown@localhost';
    private const INVALIDPLACEHOLDER = 'broken@user.local';

    public string $value;

    public function __construct(string $value) {
        parent::__construct($value, 'emailAddress', 255);
        $value = strtolower(str_replace(' ', '', $value));

        if (!self::isPlaceholder($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false) {
            $this->value = self::INVALIDPLACEHOLDER;
        } else {
            $this->value = $value;
        }

        $this->invalid = $this->invalid || (!self::isPlaceholder($value) && filter_var($value, \FILTER_VALIDATE_EMAIL) === false);
    }

    public static function makePlaceholder(): self {
        return new self(self::PLACEHOLDER);
    }

    public static function isPlaceholder(string $email): bool {
        return self::PLACEHOLDER === $email;
    }

    public static function isInvalid(string $value): bool {
        return self::INVALIDPLACEHOLDER === $value;
    }

    public static function isPlaceholderDomain(string $email): bool {
        return explode('@', self::PLACEHOLDER)[1] === $email;
    }

    public static function isInvalidDomain(string $value): bool {
        return explode('@', self::INVALIDPLACEHOLDER)[1] === $value;
    }
}
