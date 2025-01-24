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

namespace Sensor\Repository;

use Sensor\Dto\InsertEventDto;
use Sensor\Entity\EventEntity;
use Sensor\Model\Validated\Timestamp;

class EventRepository {
    public function __construct(
        private AccountRepository $accountRepository,
        private SessionRepository $sessionRepository,
        private IpAddressRepository $ipAddressRepository,
        private IspRepository $ispRepository,
        private UrlRepository $urlRepository,
        private DeviceRepository $deviceRepository,
        private RefererRepository $refererRepository,
        private EmailRepository $emailRepository,
        private DomainRepository $domainRepository,
        private PhoneRepository $phoneRepository,
        private EventCountryRepository $eventCountryRepository,
        private \PDO $pdo,
    ) {
    }

    public function insert(
        EventEntity $event,
        ?int $lastEmailId,
        ?int $lastPhoneId,
    ): InsertEventDto {
        $ipDto = $this->ipAddressRepository->insertSwitch($event->ipAddress);

        $event->country->countryId = $ipDto->countryId;
        $this->eventCountryRepository->insert($event->country);

        $deviceId = $this->deviceRepository->insert($event->device);
        $emailDto = $this->emailRepository->insertSwitch($event->email);
        $phoneId = $this->phoneRepository->insertSwitch($event->phone);

        $urlDto = $this->urlRepository->insert($event->url);
        $eventTypeId = $event->eventType !== null ? ($this->getEventType($event->eventType) ?? 1) : 1;
        $httpMethodId = $event->httpMethod !== null ? $this->getHttpMethod($event->httpMethod) : null;
        $refererId = $event->referer !== null ? $this->refererRepository->insert($event->referer) : null;

        $sessionId = $this->sessionRepository->insert($event->session);

        $eventId = $this->insertEvent(
            $event,
            $ipDto->ipAddressId,
            $urlDto->urlId,
            $deviceId,
            $refererId,
            $urlDto->queryId,
            $eventTypeId,
            $httpMethodId,
            $emailDto?->emailId,
            $phoneId,
            $sessionId,
        );

        // Update last email/phone, if changed or was empty
        if ($lastEmailId !== $emailDto?->emailId || $lastPhoneId !== $phoneId) {
            $this->accountRepository->updateLastEmailAndPhone(
                $event->accountId,
                $emailDto?->emailId ?? $lastEmailId, // Don't reset last email ID to null
                $phoneId ?? $lastPhoneId,            // Don't reset last phone ID to null
            );
        }

        return new InsertEventDto(
            $eventId,
            $ipDto->ipAddressId,
            $urlDto->urlId,
            $deviceId,
            $ipDto->countryId,
            $emailDto?->domainId,
            $ipDto->ispId,
        );
    }

    private function insertEvent(
        EventEntity $event,
        int $ipAddressId,
        int $urlId,
        int $deviceId,
        ?int $refererId,
        ?int $queryId,
        int $eventTypeId,
        ?int $httpMethodId,
        ?int $emailId,
        ?int $phoneId,
        int $sessionId,
    ): int {
        $payload = $event->payload !== null ? json_encode($event->payload) : null;

        $sql = 'INSERT INTO event
                (key, account, ip, url, device, referer, time, query, type, http_method, email, phone, http_code, traceid, payload, session_id)
            VALUES
                (:key, :account, :ip, :url, :device, :referer, :time, :query, :type, :method, :email, :phone, :http_code, :traceid, :payload, :session_id)
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $event->apiKeyId);
        $stmt->bindValue(':account', $event->accountId);
        $stmt->bindValue(':ip', $ipAddressId);
        $stmt->bindValue(':url', $urlId);
        $stmt->bindValue(':device', $deviceId);
        $stmt->bindValue(':referer', $refererId);
        $stmt->bindValue(':time', $event->eventTime->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':query', $queryId);
        $stmt->bindValue(':type', $eventTypeId);
        $stmt->bindValue(':method', $httpMethodId);
        $stmt->bindValue(':email', $emailId);
        $stmt->bindValue(':phone', $phoneId);
        $stmt->bindValue(':http_code', $event->httpCode);
        $stmt->bindValue(':traceid', $event->traceId);
        $stmt->bindValue(':payload', $payload);
        $stmt->bindValue(':session_id', $sessionId);
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }

    private function getEventType(string $eventType): ?int {
        $sql = 'SELECT id FROM event_type WHERE "value" ilike :event_type LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':event_type', $eventType);
        $stmt->execute();

        $result = $stmt->fetchColumn();

        return $result === false ? null : (int) $result;
    }

    private function getHttpMethod(string $method): ?int {
        $sql = 'SELECT id FROM event_http_method WHERE "value" ilike :method LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':method', $method);
        $stmt->execute();

        $result = $stmt->fetchColumn();

        return $result === false ? null : (int) $result;
    }
}
