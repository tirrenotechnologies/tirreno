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

namespace Sensor\Model;

class CreateEventDto {
    /**
     * @param array<mixed, mixed>|null $payload
     */
    public function __construct(
        public ?string $firstName,
        public ?string $lastName,
        public ?string $fullName,
        public ?string $pageTitle,
        public string $userName,
        public ?HashedValue $emailAddress,
        public ?string $emailDomain,
        public ?HashedValue $phoneNumber,
        public HashedValue $ipAddress,
        public string $url,
        public ?string $userAgent,
        public \DateTimeImmutable $eventTime,
        public ?string $httpReferer,
        public ?int $httpCode,
        public ?string $browserLanguage,
        public ?string $eventType,
        public ?string $httpMethod,
        public ?\DateTimeImmutable $userCreated,
        public ?string $traceId,
        public array|string|null $payload,
        public array $changedParams,
    ) {
    }
}
