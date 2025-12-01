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

namespace Sensor\Entity;

class PhoneEnrichedEntity {
    public function __construct(
        public int $accountId,
        public int $apiKeyId,
        public string $phoneNumber,
        public ?string $hash,
        public int $profiles,
        public ?int $countryId,
        public int $callingCountryCode,
        public string $nationalFormat,
        public bool $invalid,
        public ?string $validationErrors,
        public ?string $carrierName,
        public string $type,
        public ?bool $alertList,
        public bool $fraudDetected,
        public bool $checked,
        public \DateTimeImmutable $lastSeen,
    ) {
    }
}
