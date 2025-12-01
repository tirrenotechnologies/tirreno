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

namespace Sensor\Repository;

use Sensor\Entity\DomainEnrichedEntity;
use Sensor\Entity\DomainEntity;
use Sensor\Entity\DomainNotFoundEntity;
use Sensor\Model\Validated\Email;
use Sensor\Model\Validated\Timestamp;

class DomainRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function isChecked(
        string $emailDomain,
        int $apiKeyId,
    ): bool {
        $sql = 'SELECT 1 FROM event_domain WHERE key = :key AND domain = :domain AND checked = :checked LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->bindValue(':domain', $emailDomain);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);

        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function insertSwitch(DomainEntity|DomainEnrichedEntity|DomainNotFoundEntity $domain): int {
        if ($domain instanceof DomainEnrichedEntity) {
            return $this->insertEnriched($domain);
        } elseif ($domain instanceof DomainNotFoundEntity) {
            return $this->insertEnrichedNotFound($domain);
        } else {
            return $this->insert($domain);
        }
    }

    public function insert(DomainEntity $domain): int {
        // if `checked` === null -> enrichment was not performed; `checked` === false -> enrichment failed
        // set `checked` true if domain is a placeholder; TODO: same behaviour for validation errors?
        $domain->checked = Email::isPlaceholderDomain($domain->domain) || Email::isInvalidDomain($domain->domain) ? true : $domain->checked;

        $sql = 'INSERT INTO event_domain
                (key, domain, lastseen, created, updated, checked)
            VALUES
                (:key, :domain, :lastseen, :created, :updated, COALESCE(:checked, false))
            ON CONFLICT (key, domain) DO UPDATE
            SET
                lastseen = EXCLUDED.lastseen, checked = COALESCE(:checked, event_domain.checked)
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $domain->apiKeyId);
        $stmt->bindValue(':domain', $domain->domain);
        $stmt->bindValue(':lastseen', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':checked', $domain->checked, $domain->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }

    public function insertEnriched(DomainEnrichedEntity $domain): int {
        $sql = 'INSERT INTO event_domain (
                key, domain, ip, geo_ip, geo_html, web_server, hostname, emails, phone, discovery_date, blockdomains,
                disposable_domains, free_email_provider, tranco_rank, creation_date, expiration_date, return_code, disabled,
                closest_snapshot, mx_record, checked, lastseen, created, updated
            ) VALUES (
                :key, :domain, :ip, :geo_ip, :geo_html, :web_server, :hostname, :emails, :phone, :discovery_date, :blockdomains,
                :disposable_domains, :free_email_provider, :tranco_rank, :creation_date, :expiration_date, :return_code, :disabled,
                :closest_snapshot, :mx_record, :checked, :lastseen, :created, :updated)
            ON CONFLICT (key, domain) DO UPDATE
            SET
                ip = EXCLUDED.ip, geo_ip = EXCLUDED.geo_ip, geo_html = EXCLUDED.geo_html, web_server = EXCLUDED.web_server, hostname = EXCLUDED.hostname,
                emails = EXCLUDED.emails, phone = EXCLUDED.phone, discovery_date = EXCLUDED.discovery_date, blockdomains = EXCLUDED.blockdomains,
                disposable_domains = EXCLUDED.disposable_domains, free_email_provider = EXCLUDED.free_email_provider, tranco_rank = EXCLUDED.tranco_rank,
                creation_date = EXCLUDED.creation_date, expiration_date = EXCLUDED.expiration_date, return_code = EXCLUDED.return_code, disabled = EXCLUDED.disabled,
                closest_snapshot = EXCLUDED.closest_snapshot, mx_record = EXCLUDED.mx_record, checked = EXCLUDED.checked, lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $domain->apiKeyId);
        $stmt->bindValue(':domain', $domain->domain);
        $stmt->bindValue(':ip', $domain->ip);
        $stmt->bindValue(':geo_ip', $domain->geoIp);
        $stmt->bindValue(':geo_html', $domain->geoHtml);
        $stmt->bindValue(':web_server', $domain->webServer);
        $stmt->bindValue(':hostname', $domain->hostname);
        $stmt->bindValue(':emails', $domain->emails);
        $stmt->bindValue(':phone', $domain->phone);
        $stmt->bindValue(':discovery_date', $domain->discoveryDate);
        $stmt->bindValue(':blockdomains', $domain->blockdomains, \PDO::PARAM_BOOL);
        $stmt->bindValue(':disposable_domains', $domain->disposableDomains, \PDO::PARAM_BOOL);
        $stmt->bindValue(':free_email_provider', $domain->freeEmailProvider, \PDO::PARAM_BOOL);
        $stmt->bindValue(':tranco_rank', $domain->trancoRank);
        $stmt->bindValue(':creation_date', $domain->creationDate);
        $stmt->bindValue(':expiration_date', $domain->expirationDate);
        $stmt->bindValue(':return_code', $domain->returnCode);
        $stmt->bindValue(':disabled', $domain->disabled, \PDO::PARAM_BOOL);
        $stmt->bindValue(':closest_snapshot', $domain->closestSnapshot);
        $stmt->bindValue(':mx_record', $domain->mxRecord, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }

    public function insertEnrichedNotFound(DomainNotFoundEntity $domain): int {
        $sql = 'INSERT INTO event_domain (
            key, domain, blockdomains, disposable_domains, free_email_provider, creation_date, expiration_date,
            return_code, disabled, closest_snapshot, mx_record, checked, lastseen, created, updated
        ) VALUES (
            :key, :domain, :blockdomains, :disposable_domains, :free_email_provider, :creation_date, :expiration_date,
            :return_code, :disabled, :closest_snapshot, :mx_record, :checked, :lastseen, :created, :updated)
        ON CONFLICT (key, domain) DO UPDATE
        SET
            blockdomains = EXCLUDED.blockdomains, disposable_domains = EXCLUDED.disposable_domains,
            free_email_provider = EXCLUDED.free_email_provider, creation_date = EXCLUDED.creation_date,
            expiration_date = EXCLUDED.expiration_date, return_code = EXCLUDED.return_code, disabled = EXCLUDED.disabled,
            closest_snapshot = EXCLUDED.closest_snapshot, mx_record = EXCLUDED.mx_record, checked = EXCLUDED.checked,
            lastseen = EXCLUDED.lastseen
        RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $domain->apiKeyId);
        $stmt->bindValue(':domain', $domain->domain);
        $stmt->bindValue(':blockdomains', $domain->blockdomains, \PDO::PARAM_BOOL);
        $stmt->bindValue(':disposable_domains', $domain->disposableDomains, \PDO::PARAM_BOOL);
        $stmt->bindValue(':free_email_provider', $domain->freeEmailProvider, \PDO::PARAM_BOOL);
        $stmt->bindValue(':creation_date', $domain->creationDate);
        $stmt->bindValue(':expiration_date', $domain->expirationDate);
        $stmt->bindValue(':return_code', $domain->returnCode);
        $stmt->bindValue(':disabled', $domain->disabled, \PDO::PARAM_BOOL);
        $stmt->bindValue(':closest_snapshot', $domain->closestSnapshot);
        $stmt->bindValue(':mx_record', $domain->mxRecord, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $domain->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }
}
