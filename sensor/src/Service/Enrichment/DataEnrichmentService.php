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

namespace Sensor\Service\Enrichment;

use Sensor\Dto\GetApiKeyDto;
use Sensor\Exception\AuthException;
use Sensor\Exception\ForbiddenException;
use Sensor\Exception\RateLimitException;
use Sensor\Factory\EnrichedDataFactory;
use Sensor\Model\Config\Config;
use Sensor\Model\Enriched\EnrichedData;
use Sensor\Model\HashedValue;
use Sensor\Model\Validated\Email;
use Sensor\Model\Validated\IpAddress;
use Sensor\Repository\DomainRepository;
use Sensor\Repository\EmailRepository;
use Sensor\Repository\IpAddressRepository;
use Sensor\Repository\PhoneRepository;
use Sensor\Service\Logger;
use Sensor\Service\Profiler;

class DataEnrichmentService {
    public function __construct(
        private DataEnrichmentClientInterface $dataEnrichmentClient,
        private EnrichedDataFactory $enrichedDataFactory,
        private IpAddressRepository $ipAddressRepository,
        private EmailRepository $emailRepository,
        private DomainRepository $domainRepository,
        private PhoneRepository $phoneRepository,
        private Config $config,
        private Profiler $profiler,
        private Logger $logger,
    ) {
    }

    public function getEnrichmentData(
        GetApiKeyDto $apiKeyDto,
        ?int $accountId,
        HashedValue $ipAddress,
        ?HashedValue $email,
        ?string $emailDomain,
        ?HashedValue $phoneNumber,
    ): ?EnrichedData {
        if ($apiKeyDto->token === null) {
            return null;
        }

        $isPlaceholderOrNull = $email === null || $emailDomain === null || Email::isPlaceholder($email->value) || Email::isInvalid($email->value);

        // Check if ip/email/phone exists in the DB
        $skipIp = $apiKeyDto->skipEnrichingIps || IpAddress::isInvalid($ipAddress->value) || $ipAddress->localhost || $this->ipAddressRepository->existsForApiKey($ipAddress->value, $apiKeyDto->id);
        $skipEmail = $isPlaceholderOrNull || $apiKeyDto->skipEnrichingEmails || ($accountId !== null && $this->emailRepository->existsForAccount($email->value, $accountId, $apiKeyDto->id));
        $skipDomain = $isPlaceholderOrNull || $apiKeyDto->skipEnrichingDomains || $this->domainRepository->isChecked($emailDomain, $apiKeyDto->id);
        $skipPhone = $apiKeyDto->skipEnrichingPhones || $phoneNumber === null || ($accountId !== null && $this->phoneRepository->existsForAccount($phoneNumber->value, $accountId, $apiKeyDto->id));

        // Enrich data, only if it's missing
        return $this->query(
            $apiKeyDto->token,
            $apiKeyDto->hashExchange,
            $skipIp ? null : $ipAddress,
            $skipEmail ? null : $email,
            $skipPhone ? null : $phoneNumber,
            $skipDomain ? null : $emailDomain,
        );
    }

    private function query(
        string $token,
        bool $hashExchange,
        ?HashedValue $ipAddress,
        ?HashedValue $email,
        ?HashedValue $phone,
        ?string $emailDomain,
    ): ?EnrichedData {
        $query = array_filter([
            'email' => $email?->toArray($hashExchange),
            'ip' => $ipAddress?->toArray($hashExchange),
            'phone' => $phone?->toArray($hashExchange),
            'domain' => $emailDomain,
        ], static function ($value): bool {
            return $value !== null;
        });

        if (!count($query)) {
            $this->logger->logDebug('Skipping calling enrichment API, because data is already enriched or skipped');

            try {
                $this->dataEnrichmentClient->track($token);
            } catch (\Throwable $e) {
                $this->logger->logError($e, 'Error sending track event for the enrichment API');
            }

            return null;
        }

        try {
            $this->profiler->start('api');
            $response = $this->dataEnrichmentClient->query((array) $query, $token);
            $this->profiler->finish('api');
        } catch (AuthException $e) {
            $this->logger->logError($e, sprintf('Enrichment API returned authorization error (%d): %s', $e->getCode(), $e->getMessage()));

            return null;
        } catch (ForbiddenException $e) {
            return new EnrichedData(null, null, null, null, null, false);
        } catch (RateLimitException $e) {
            return new EnrichedData(null, null, null, null, null, true);
        } catch (\Throwable $e) {
            $this->logger->logError($e, 'Unable to connect to the enrichment API: ' . $e->getMessage());

            return null;
        }

        $debug = [
            'url' => $this->config->enrichmentApiUrl,
            'key' => $this->config->enrichmentApiKey,
            'data' => $query,
        ];
        $this->logger->logDebug('Calling enrichment API with data: ' . json_encode($debug));

        return $this->enrichedDataFactory->createFromResponse($response, $query);
    }
}
