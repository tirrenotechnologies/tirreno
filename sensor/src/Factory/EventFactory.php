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

namespace Sensor\Factory;

use Sensor\Entity\DeviceEntity;
use Sensor\Entity\DomainEnrichedEntity;
use Sensor\Entity\DomainEntity;
use Sensor\Entity\DomainNotFoundEntity;
use Sensor\Entity\EmailEnrichedEntity;
use Sensor\Entity\EmailEntity;
use Sensor\Entity\EventEntity;
use Sensor\Entity\IpAddressEnrichedEntity;
use Sensor\Entity\IpAddressLocalhostEnrichedEntity;
use Sensor\Entity\IpAddressEntity;
use Sensor\Entity\CountryEntity;
use Sensor\Entity\IspEntity;
use Sensor\Entity\IspEnrichedEntity;
use Sensor\Entity\IspLocalhostEntity;
use Sensor\Entity\PhoneEnrichedEntity;
use Sensor\Entity\PhoneEntity;
use Sensor\Entity\PhoneInvalidEntity;
use Sensor\Entity\RefererEntity;
use Sensor\Entity\SessionEntity;
use Sensor\Entity\UrlEntity;
use Sensor\Entity\UrlQueryEntity;
use Sensor\Entity\UserAgentEnrichedEntity;
use Sensor\Entity\UserAgentEntity;
use Sensor\Entity\PayloadEntity;
use Sensor\Model\Blacklist\FraudDetected;
use Sensor\Model\CreateEventDto;
use Sensor\Model\DeviceDetected;
use Sensor\Repository\CountryRepository;
use Sensor\Model\Enriched\EnrichedData;
use Sensor\Model\Enriched\IpAddressEnriched;
use Sensor\Model\Enriched\IpAddressLocalhostEnriched;
use Sensor\Model\Enriched\IpAddressEnrichFailed;
use Sensor\Model\Enriched\PhoneEnriched;
use Sensor\Model\Enriched\PhoneInvalidEnriched;
use Sensor\Model\Enriched\PhoneEnrichFailed;
use Sensor\Model\Enriched\DomainEnriched;
use Sensor\Model\Enriched\DomainNotFoundEnriched;
use Sensor\Model\Enriched\DomainEnrichFailed;
use Sensor\Model\Enriched\EmailEnriched;
use Sensor\Model\Enriched\EmailEnrichFailed;

class EventFactory {
    public function __construct(
        private CountryRepository $countryRepository,
    ) {
    }

    public function createFromDto(
        int $accountId,
        int $sessionId,
        int $apiKeyId,
        CreateEventDto $dto,
        ?EnrichedData $enrichedData,
        FraudDetected $fraudDetected,
        ?DeviceDetected $deviceDetected,
        ?string $query,
    ): EventEntity {
        $lastSeen = $dto->eventTime;

        // Remove query from url
        $urlWithoutQuery = $query !== null ? str_replace($query, '', $dto->url) : $dto->url;

        $queryEntity = null;
        if ($query !== null) {
            $queryEntity = new UrlQueryEntity($apiKeyId, $query, $lastSeen);
        }
        $url = new UrlEntity($apiKeyId, $urlWithoutQuery, $queryEntity, $dto->pageTitle, $dto->httpCode, $lastSeen);
        $eventType = $dto->eventType;
        $httpMethod = $dto->httpMethod;

        if ($dto->httpReferer !== null) {
            $referer = new RefererEntity($apiKeyId, $dto->httpReferer, $lastSeen);
        }

        $session = new SessionEntity($sessionId, $accountId, $apiKeyId, $lastSeen);

        // set countryId after inserting ip
        $country = new CountryEntity($apiKeyId, 0, $lastSeen);

        $ipAddress = null;
        if ($dto->ipAddress->localhost) {
            // sensor-defined localhost
            $isp = new IspLocalhostEntity($apiKeyId, $lastSeen);
            $ipAddress = new IpAddressLocalhostEnrichedEntity(
                $apiKeyId,
                $dto->ipAddress->value,
                $dto->ipAddress->hash,
                $fraudDetected->ip,
                $isp,
                $lastSeen,
            );
        } else {
            if ($enrichedData?->ip instanceof IpAddressEnriched || $enrichedData?->ip instanceof IpAddressLocalhostEnriched) {
                // isp IspEnriched or IspEnrichedEmpty
                // it's a new ip, checked is true; should be INSERTed or UPDATEd
                $countryId = $this->countryRepository->getCountryIdByCode($enrichedData->ip->countryCode);
                $isp = new IspEnrichedEntity(
                    $apiKeyId,
                    $enrichedData->isp->asn,
                    $enrichedData->isp->name,
                    $enrichedData->isp->description,
                    $lastSeen,
                );
                $ipAddress = new IpAddressEnrichedEntity(
                    $apiKeyId,
                    $enrichedData->ip->ipAddress,
                    $dto->ipAddress->hash,
                    $countryId,
                    $enrichedData->ip->hosting,
                    $enrichedData->ip->vpn,
                    $enrichedData->ip->tor,
                    $enrichedData->ip->relay,
                    $enrichedData->ip->starlink,
                    $enrichedData->ip->blocklist,
                    $enrichedData->ip->domainsCount,
                    $enrichedData->ip->cidr,
                    $enrichedData->ip->alertList,
                    $fraudDetected->ip,
                    $isp,
                    true,
                    $lastSeen,
                );
            } elseif ($enrichedData?->ip instanceof IpAddressEnrichFailed) {
                // checked can be false or true on bogon ip; should be INSERTed or UPDATEd (if it is reenrichment)
                // isp made of IspEnrichedLocalhost or IspEnrichedEmpty
                $isp = new IspEnrichedEntity(
                    $apiKeyId,
                    $enrichedData->isp->asn,
                    $enrichedData->isp->name,
                    $enrichedData->isp->description,
                    $lastSeen,
                );
                $ipAddress = new IpAddressEntity(
                    $apiKeyId,
                    $enrichedData->ip->ipAddress,
                    $dto->ipAddress->hash,
                    $fraudDetected->ip,
                    $isp,
                    $enrichedData->ip->checked,
                    $lastSeen,
                );
            } else {
                // ip already exists and has checked true or enrichment is off; should be UPDATEd or INSERTed if enrichment is off
                // isp unknown, N/A will be used if enrichment is off or failed
                $isp = new IspEntity(
                    $apiKeyId,
                    $lastSeen,
                );
                $ipAddress = new IpAddressEntity(
                    $apiKeyId,
                    $dto->ipAddress->value,
                    $dto->ipAddress->hash,
                    $fraudDetected->ip,
                    $isp,
                    null,                   // enrichment is off or was enriched earlier
                    $lastSeen,
                );
            }
        }

        $domain = null;
        if ($dto->emailDomain !== null) {
            if ($enrichedData?->domain instanceof DomainEnriched) {
                $domain = new DomainEnrichedEntity(
                    $apiKeyId,
                    $enrichedData->domain->domain,
                    $enrichedData->domain->blockdomains,
                    $enrichedData->domain->disposableDomains,
                    $enrichedData->domain->freeEmailProvider,
                    $enrichedData->domain->ip,
                    $enrichedData->domain->geoIp,
                    $enrichedData->domain->geoHtml,
                    $enrichedData->domain->webServer,
                    $enrichedData->domain->hostname,
                    $enrichedData->domain->emails,
                    $enrichedData->domain->phone,
                    $enrichedData->domain->discoveryDate,
                    $enrichedData->domain->trancoRank,
                    $enrichedData->domain->creationDate,
                    $enrichedData->domain->expirationDate,
                    $enrichedData->domain->returnCode,
                    $enrichedData->domain->disabled,
                    $enrichedData->domain->closestSnapshot,
                    $enrichedData->domain->mxRecord,
                    true,
                    $lastSeen,
                );
            } elseif ($enrichedData?->domain instanceof DomainNotFoundEnriched) {
                $domain = new DomainNotFoundEntity(
                    $apiKeyId,
                    $enrichedData->domain->domain,
                    $enrichedData->domain->blockdomains,
                    $enrichedData->domain->disposableDomains,
                    $enrichedData->domain->freeEmailProvider,
                    $enrichedData->domain->creationDate,
                    $enrichedData->domain->expirationDate,
                    $enrichedData->domain->returnCode,
                    $enrichedData->domain->disabled,
                    $enrichedData->domain->closestSnapshot,
                    $enrichedData->domain->mxRecord,
                    true,
                    $lastSeen,
                );
            } elseif ($enrichedData?->domain instanceof DomainEnrichFailed) {
                $domain = new DomainEntity(
                    $apiKeyId,
                    $enrichedData->domain->domain,
                    $enrichedData->domain->checked,
                    $lastSeen,
                );
            } else {
                $domain = new DomainEntity(
                    $apiKeyId,
                    $dto->emailDomain,
                    null,               // enrichment is off or was enriched earlier
                    $lastSeen,
                );
            }
        }

        $email = null;
        if ($dto->emailAddress !== null && $domain !== null) {
            if ($enrichedData?->email instanceof EmailEnriched) {
                $email = new EmailEnrichedEntity(
                    $accountId,
                    $apiKeyId,
                    $enrichedData->email->email,
                    $dto->emailAddress->hash,
                    $domain,
                    $enrichedData->email->blockEmails,
                    $enrichedData->email->dataBreach,
                    $enrichedData->email->dataBreaches,
                    $enrichedData->email->earliestBreach,
                    $enrichedData->email->profiles,
                    $enrichedData->email->domainContactEmail,
                    $enrichedData->email->alertList,
                    $fraudDetected->email,
                    true,
                    $lastSeen,
                );
            } elseif ($enrichedData?->email instanceof EmailEnrichFailed) {
                $email = new EmailEntity(
                    $accountId,
                    $apiKeyId,
                    $enrichedData->email->email,
                    $dto->emailAddress->hash,
                    $domain,
                    $fraudDetected->email,
                    $enrichedData->email->checked,
                    $lastSeen,
                );
            } else {
                $email = new EmailEntity(
                    $accountId,
                    $apiKeyId,
                    $dto->emailAddress->value,
                    $dto->emailAddress->hash,
                    $domain,
                    $fraudDetected->email,
                    null,                       // enrichment is off or was enriched earlier
                    $lastSeen,
                );
            }
        }

        $phone = null;
        if ($dto->phoneNumber !== null) {
            $countryId = 0;
            if ($enrichedData?->phone instanceof PhoneEnriched) {
                if ($enrichedData->phone->countryCode !== null) {
                    $countryId = $this->countryRepository->getCountryIdByCode($enrichedData->phone->countryCode);
                }
                $phone = new PhoneEnrichedEntity(
                    $accountId,
                    $apiKeyId,
                    $enrichedData->phone->phoneNumber,
                    $dto->phoneNumber->hash,
                    $enrichedData->phone->profiles,
                    $countryId,
                    $enrichedData->phone->callingCountryCode,
                    $enrichedData->phone->nationalFormat,
                    $enrichedData->phone->invalid,
                    $enrichedData->phone->validationErrors,
                    $enrichedData->phone->carrierName,
                    $enrichedData->phone->type,
                    $enrichedData->phone->alertList,
                    $fraudDetected->phone,
                    true,
                    $lastSeen,
                );
            } elseif ($enrichedData?->phone instanceof PhoneInvalidEnriched) {
                $phone = new PhoneInvalidEntity(
                    $accountId,
                    $apiKeyId,
                    $enrichedData->phone->phoneNumber,
                    $dto->phoneNumber->hash,
                    $countryId,
                    $fraudDetected->phone,
                    $enrichedData->phone->validationErrors,
                    true,
                    $lastSeen,
                );
            } elseif ($enrichedData?->phone instanceof PhoneEnrichFailed) {
                $phone = new PhoneEntity(
                    $accountId,
                    $apiKeyId,
                    $enrichedData->phone->phoneNumber,
                    $dto->phoneNumber->hash,
                    $countryId,
                    $fraudDetected->phone,
                    $enrichedData->phone->checked,
                    $lastSeen,
                );
            } else {
                $phone = new PhoneEntity(
                    $accountId,
                    $apiKeyId,
                    $dto->phoneNumber->value,
                    $dto->phoneNumber->hash,
                    $countryId,                     // set country to 0 if enrichment is off or keep existing country in query
                    $fraudDetected->phone,
                    null,                           // enrichment is off or was enriched earlier
                    $lastSeen,
                );
            }
        }

        $userAgent = null;
        if ($deviceDetected instanceof DeviceDetected) {
            $userAgent = new UserAgentEnrichedEntity(
                $apiKeyId,
                $deviceDetected->userAgent,
                $deviceDetected->device,
                $deviceDetected->browserName,
                $deviceDetected->browserVersion,
                $deviceDetected->osName,
                $deviceDetected->osVersion,
                $deviceDetected->modified,
                true,
                $lastSeen,
            );
        } else {
            $userAgent = new UserAgentEntity(
                $apiKeyId,
                $dto->userAgent,
                null,               // ua is null, was enriched earlier or enrichment is off
                $lastSeen,
            );
        }

        $device = new DeviceEntity($accountId, $apiKeyId, $userAgent, $dto->browserLanguage, $lastSeen);

        $payload = ($dto->payload) ? (new PayloadEntity($accountId, $apiKeyId, $dto->payload, $lastSeen)) : null;

        return new EventEntity(
            $accountId,
            $session,
            $apiKeyId,
            $ipAddress,
            $url,
            $eventType,
            $httpMethod,
            $device,
            $referer ?? null,
            $email ?? null,
            $phone ?? null,
            $dto->httpCode,
            $dto->eventTime,
            $dto->traceId,
            $payload,
            $country,
        );
    }
}
