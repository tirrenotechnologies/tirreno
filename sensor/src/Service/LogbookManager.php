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

namespace Sensor\Service;

use Sensor\Dto\GetApiKeyDto;
use Sensor\Factory\LogbookEntityFactory;
use Sensor\Model\Http\ErrorResponse;
use Sensor\Model\Http\RegularResponse;
use Sensor\Repository\ApiKeyRepository;
use Sensor\Repository\EventIncorrectRepository;
use Sensor\Repository\LogbookRepository;
use Sensor\Exception\RateLimitException;

class LogbookManager {
    private ?GetApiKeyDto $apiKeyDto = null;

    public function __construct(
        private LogbookEntityFactory $logbookFactory,
        private LogbookRepository $logbookRepository,
        private ApiKeyRepository $apiKeyRepository,
        private EventIncorrectRepository $eventIncorrectRepository,
        private bool $allowEmailPhone,
        private int $leakyBucketRps,
        private int $leakyBucketWindow,
    ) {
    }

    public function logRequest(
        \DateTime $startedTime,
        RegularResponse|ErrorResponse $response,
    ): void {
        if ($this->apiKeyDto?->id !== null) {
            $logbook = $this->logbookFactory->create(
                $this->apiKeyDto->id,
                $startedTime,
                $response,
            );
            $this->logbookRepository->insert($logbook);
        }
    }

    public function logException(
        \DateTime $startedTime,
        string $exception,
        bool $rateLimit,
    ): void {
        if ($this->apiKeyDto?->id !== null) {
            $logbook = $this->logbookFactory->createFromException(
                $this->apiKeyDto->id,
                $startedTime,
                $exception,
                $rateLimit,
            );
            $this->logbookRepository->insert($logbook);
        }
    }

    public function logIncorrectRequest(array $payload, string $error, ?string $traceId): void {
        $this->eventIncorrectRepository->logIncorrectEvent(
            $payload,
            $error,
            $traceId,
            $this->apiKeyDto?->id,
        );
    }

    public function getApiKeyDto(?string $apiKeyString): ?GetApiKeyDto {
        return $apiKeyString !== null ? $this->apiKeyRepository->getApiKey($apiKeyString, $this->allowEmailPhone) : null;
    }

    public function setApiKeyDto(?GetApiKeyDto $apiKeyDto): void {
        $this->apiKeyDto = $apiKeyDto;
    }

    public function checkRps(): void {
        if (isset($this->apiKeyDto) && $this->apiKeyDto?->id) {
            if (!$this->logbookRepository->checkRps($this->leakyBucketRps, $this->leakyBucketWindow, $this->apiKeyDto->id)) {
                throw new RateLimitException('Rate limit', 429);
            }
        }
    }
}
