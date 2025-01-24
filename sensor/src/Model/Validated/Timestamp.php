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

namespace Sensor\Model\Validated;

class Timestamp extends Base {
    private const INVALIDPLACEHOLDER = '1970-01-01 00:00:00.000';
    public const EVENTFORMAT = 'Y-m-d H:i:s.v';
    public const FORMAT = 'Y-m-d H:i:s';
    public const MICROSECONDS = 'Y-m-d H:i:s.u';

    public \DateTimeImmutable $value;

    public function __construct(string $value) {
        parent::__construct($value, 'timestamp');
        $invalid = false;

        try {
            $val = \DateTimeImmutable::createFromFormat(self::EVENTFORMAT, $value);
        } catch (\Throwable $e) {
            // \DateTimeImmutable::createFromFormat throws ValueError when the datetime contains NULL-bytes
            $invalid = true;
            $val = new \DateTimeImmutable(self::INVALIDPLACEHOLDER);
        }

        if ($val === false) {
            $val = \DateTimeImmutable::createFromFormat(self::FORMAT, $value);
        }

        if ($val === false) {
            $val = \DateTimeImmutable::createFromFormat(self::MICROSECONDS, $value);
        }

        if ($val === false) {
            $invalid = true;
            $val = new \DateTimeImmutable(self::INVALIDPLACEHOLDER);
        }

        $this->value = $val;
        $this->invalid = $invalid;
    }
}
