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

use Sensor\Dto\InsertEmailDto;
use Sensor\Entity\DomainEnrichedEntity;
use Sensor\Entity\DomainEntity;
use Sensor\Entity\DomainNotFoundEntity;
use Sensor\Entity\EmailEnrichedEntity;
use Sensor\Entity\EmailEntity;
use Sensor\Model\Validated\Email;
use Sensor\Model\Validated\Timestamp;

class EmailRepository {
    public function __construct(
        private DomainRepository $domainRepository,
        private \PDO $pdo,
    ) {
    }

    public function existsForAccount(
        string $email,
        int $accountId,
        int $apiKeyId,
    ): bool {
        $sql = 'SELECT 1 FROM event_email WHERE account_id = :account_id AND key = :key AND email = :email AND checked = :checked LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $accountId);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);

        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function insertSwitch(EmailEnrichedEntity|EmailEntity|null $email): ?InsertEmailDto {
        if ($email !== null) {
            return $email instanceof EmailEntity ? $this->insert($email) : $this->insertEnriched($email);
        }

        return null;
    }

    // enrichment failed || enriment off || already enriched
    public function insert(EmailEntity $email): InsertEmailDto {
        // insert/update domain first to extract domain id
        $domainId = $this->domainRepository->insertSwitch($email->domain);

        // if `checked` === null -> enrichment was not performed; `checked` === false -> enrichment failed
        // set `checked` true if email is a placeholder
        $email->checked = Email::isPlaceholder($email->email) || Email::isInvalid($email->email) ? true : $email->checked;

        $sql = 'INSERT INTO event_email
                (account_id, key, email, hash, domain, fraud_detected, lastseen, created, checked)
            VALUES
                (:account_id, :key, :email, :hash, :domain, :fraud_detected, :lastseen, :created, COALESCE(:checked, false))
            ON CONFLICT (account_id, email) DO UPDATE
            SET
                hash = EXCLUDED.hash, domain = EXCLUDED.domain, fraud_detected = EXCLUDED.fraud_detected,
                lastseen = EXCLUDED.lastseen, checked = COALESCE(:checked, event_email.checked)
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $email->accountId);
        $stmt->bindValue(':key', $email->apiKeyId);
        $stmt->bindValue(':email', $email->email);
        $stmt->bindValue(':hash', $email->hash);
        $stmt->bindValue(':domain', $domainId);
        $stmt->bindValue(':fraud_detected', $email->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', $email->checked, $email->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $email->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $email->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return new InsertEmailDto($result['id'], $domainId);
    }

    public function insertEnriched(EmailEnrichedEntity $email): InsertEmailDto {
        // insert/update domain first to extract domain id
        $domainId = $this->domainRepository->insertSwitch($email->domain);

        $sql = 'INSERT INTO event_email (
                account_id, key, email, hash, blockemails, data_breach, profiles, domain_contact_email, domain, checked,
                data_breaches, earliest_breach, alert_list, fraud_detected, lastseen, created
            ) VALUES (
                :account_id, :key, :email, :hash, :blockemails, :data_breach, :profiles, :domain_contact_email, :domain, :checked,
                :data_breaches, :earliest_breach, :alert_list, :fraud_detected, :lastseen, :created)
            ON CONFLICT (account_id, email) DO UPDATE
            SET
                hash = EXCLUDED.hash, blockemails = EXCLUDED.blockemails, data_breach = EXCLUDED.data_breach, profiles = EXCLUDED.profiles,
                domain_contact_email = EXCLUDED.domain_contact_email, domain = EXCLUDED.domain, data_breaches = EXCLUDED.data_breaches,
                earliest_breach = EXCLUDED.earliest_breach, alert_list = EXCLUDED.alert_list, fraud_detected = EXCLUDED.fraud_detected,
                checked = EXCLUDED.checked, lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $email->accountId);
        $stmt->bindValue(':key', $email->apiKeyId);
        $stmt->bindValue(':email', $email->email);
        $stmt->bindValue(':hash', $email->hash);
        $stmt->bindValue(':blockemails', $email->blockEmails, \PDO::PARAM_BOOL);
        $stmt->bindValue(':data_breach', $email->dataBreach, \PDO::PARAM_BOOL);
        $stmt->bindValue(':profiles', $email->profiles);
        $stmt->bindValue(':domain_contact_email', $email->domainContactEmail, \PDO::PARAM_BOOL);
        $stmt->bindValue(':domain', $domainId);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':data_breaches', $email->dataBreaches);
        $stmt->bindValue(':earliest_breach', $email->earliestBreach?->format('Y-m-d'));
        $stmt->bindValue(':alert_list', $email->alertList, \PDO::PARAM_BOOL);
        $stmt->bindValue(':fraud_detected', $email->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $email->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $email->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return new InsertEmailDto($result['id'], $domainId);
    }
}
