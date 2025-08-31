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

namespace Sensor\Controller;

use Sensor\Dto\GetApiKeyDto;
use Sensor\Dto\InsertAccountDto;
use Sensor\Dto\InsertEventDto;
use Sensor\Entity\AccountEntity;
use Sensor\Exception\ValidationException;
use Sensor\Factory\EventFactory;
use Sensor\Factory\RequestFactory;
use Sensor\Model\CreateEventDto;
use Sensor\Model\Http\RegularResponse;
use Sensor\Model\Http\ErrorResponse;
use Sensor\Model\Http\Request;
use Sensor\Model\Http\ValidationFailedResponse;
use Sensor\Repository\AccountRepository;
use Sensor\Repository\ApiKeyRepository;
use Sensor\Repository\EventRepository;
use Sensor\Service\ConnectionService;
use Sensor\Service\Enrichment\DataEnrichmentService;
use Sensor\Service\DeviceDetectorService;
use Sensor\Service\FraudDetectionService;
use Sensor\Service\Logger;
use Sensor\Service\Profiler;
use Sensor\Service\QueryParser;

class CreateEventController {
    public function __construct(
        private RequestFactory $requestFactory,
        private EventFactory $eventFactory,
        private ConnectionService $connectionService,
        private QueryParser $queryParser,
        private ?DataEnrichmentService $dataEnrichmentService,
        private DeviceDetectorService $deviceDetectorService,
        private FraudDetectionService $fraudDetectionService,
        private ApiKeyRepository $apiKeyRepository,
        private EventRepository $eventRepository,
        private AccountRepository $accountRepository,
        private \PDO $pdo,
        private Profiler $profiler,
        private Logger $logger,
    ) {
    }

    public function index(Request $request, ?GetApiKeyDto $apiKeyDto): RegularResponse|ErrorResponse {
        $this->logger->logDebug('Request: ' . json_encode($request->body));

        $this->profiler->start('user');

        try {
            $dto = $this->requestFactory->createFromArray($request->body, $request->traceId, $this->eventRepository);
        } catch (ValidationException $e) {
            return new ValidationFailedResponse($e);
        }

        if ($request->apiKey === null) {
            return new ErrorResponse('Api-Key header is not set', 401);
        }

        if ($apiKeyDto === null) {
            return new ErrorResponse('API key from the "Api-Key" header is not found', 401);
        }

        $this->connectionService->finishRequestForUser();
        $this->profiler->finish('user');

        // Get account ID
        $account = new AccountEntity(
            $apiKeyDto->id,
            $dto->userName,
            $dto->ipAddress->value,
            $dto->fullName,
            $dto->firstName,
            $dto->lastName,
            $dto->eventTime,
            $dto->userCreated,
        );

        $accountDto = $this->accountRepository->checkExistence($account);

        if ($this->dataEnrichmentService === null) {
            $this->logger->logDebug('Skipping calling enrichment API, because it\'s not configured');
        }
        $enrichedData = $this->dataEnrichmentService?->getEnrichmentData(
            $apiKeyDto,
            $accountDto?->accountId,
            $dto->ipAddress,        // check localhost inside of getEnrichmentData()
            $dto->emailAddress,
            $dto->emailDomain,
            $dto->phoneNumber,
        );

        // Check if ip/email/phone is blacklisted
        $fraudDetected = $this->fraudDetectionService->getEarlierDetectedFraud(
            $apiKeyDto->id,
            $dto->emailAddress?->value,
            $dto->ipAddress->value,
            $dto->phoneNumber?->value,
        );

        $deviceDetected = $this->deviceDetectorService->parse($apiKeyDto, $dto->userAgent);

        $query = $this->queryParser->getQueryFromUrl($dto->url);

        // Insert account and event into single transaction
        $this->pdo->beginTransaction();
        try {
            $this->apiKeyRepository->updateApiCallReached($enrichedData?->reached, $apiKeyDto);

            $accountDto = $this->accountRepository->insert($account);

            $event = $this->eventFactory->createFromDto(
                $accountDto->accountId,
                $accountDto->sessionId,
                $apiKeyDto->id,
                $dto,
                $enrichedData,
                $fraudDetected,
                $deviceDetected,
                $query,
            );

            $this->profiler->start('db_insert');
            $insertDto = $this->eventRepository->insert(
                $event,
                $accountDto->lastEmailId,
                $accountDto->lastPhoneId,
            );
            $this->profiler->finish('db_insert');

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return new RegularResponse($insertDto->eventId, $dto->changedParams);
    }
}
