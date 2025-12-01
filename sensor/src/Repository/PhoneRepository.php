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

use Sensor\Entity\PhoneEnrichedEntity;
use Sensor\Entity\PhoneEntity;
use Sensor\Entity\PhoneInvalidEntity;
use Sensor\Model\Validated\Timestamp;

class PhoneRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function existsForAccount(
        string $phoneNumber,
        int $accountId,
        int $apiKeyId,
    ): bool {
        $sql = 'SELECT 1 FROM event_phone WHERE account_id = :account_id AND key = :key AND phone_number = :phone_number AND checked = :checked LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $accountId);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->bindValue(':phone_number', $phoneNumber);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);

        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function insertSwitch(PhoneEnrichedEntity|PhoneInvalidEntity|PhoneEntity|null $phone): ?int {
        if ($phone instanceof PhoneEnrichedEntity) {
            return $this->insertEnriched($phone);
        } elseif ($phone instanceof PhoneInvalidEntity) {
            return $this->insertEnrichedInvalid($phone);
        } elseif ($phone instanceof PhoneEntity) {
            return $this->insert($phone);
        }

        return null;
    }

    public function insert(PhoneEntity $phone): int {
        // if `checked` === null -> enrichment was not performed; `checked` === false -> enrichment failed
        $sql = 'INSERT INTO event_phone
                (account_id, key, phone_number, hash, country_code, fraud_detected, lastseen, created, updated, checked)
            VALUES
                (:account_id, :key, :phone_number, :hash, :country_code, :fraud_detected, :lastseen, :created, :updated, COALESCE(:checked, false))
            ON CONFLICT (key, account_id, phone_number) DO UPDATE
            SET
                hash = EXCLUDED.hash, fraud_detected = EXCLUDED.fraud_detected, lastseen = EXCLUDED.lastseen,
                country_code = CASE WHEN event_phone.country_code != 0 THEN event_phone.country_code ELSE EXCLUDED.country_code END,
                checked = COALESCE(:checked, event_phone.checked)
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $phone->accountId);
        $stmt->bindValue(':key', $phone->apiKeyId);
        $stmt->bindValue(':phone_number', $phone->phoneNumber);
        $stmt->bindValue(':hash', $phone->hash);
        $stmt->bindValue(':country_code', $phone->countryId);
        $stmt->bindValue(':fraud_detected', $phone->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', $phone->checked, $phone->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }

    public function insertEnriched(PhoneEnrichedEntity $phone): int {
        $validationErrors = $phone->validationErrors !== null ? json_encode([$phone->validationErrors], \JSON_THROW_ON_ERROR) : null;

        $sql = 'INSERT INTO event_phone (
                account_id, key, phone_number, hash, country_code, calling_country_code, national_format, invalid,
                validation_errors, carrier_name, type, profiles, alert_list, checked, fraud_detected, lastseen, created, updated
            ) VALUES (
                :account_id, :key, :phone_number, :hash, :country_code, :calling_country_code, :national_format, :invalid,
                :validation_errors, :carrier_name, :type, :profiles, :alert_list, :checked, :fraud_detected, :lastseen, :created, :updated)
            ON CONFLICT (key, account_id, phone_number) DO UPDATE
            SET
                hash = EXCLUDED.hash, country_code = EXCLUDED.country_code, calling_country_code = EXCLUDED.calling_country_code,
                national_format = EXCLUDED.national_format, invalid = EXCLUDED.invalid, validation_errors = EXCLUDED.validation_errors,
                carrier_name = EXCLUDED.carrier_name, type = EXCLUDED.type, profiles = EXCLUDED.profiles, alert_list = EXCLUDED.alert_list,
                checked = EXCLUDED.checked, fraud_detected = EXCLUDED.fraud_detected, lastseen = EXCLUDED.lastseen
        RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $phone->accountId);
        $stmt->bindValue(':key', $phone->apiKeyId);
        $stmt->bindValue(':phone_number', $phone->phoneNumber);
        $stmt->bindValue(':hash', $phone->hash);
        $stmt->bindValue(':country_code', $phone->countryId);
        $stmt->bindValue(':calling_country_code', $phone->callingCountryCode);
        $stmt->bindValue(':national_format', $phone->nationalFormat);
        $stmt->bindValue(':invalid', $phone->invalid, \PDO::PARAM_BOOL);
        $stmt->bindValue(':validation_errors', $validationErrors);
        $stmt->bindValue(':carrier_name', $phone->carrierName);
        $stmt->bindValue(':type', $phone->type);
        $stmt->bindValue(':profiles', $phone->profiles);
        $stmt->bindValue(':alert_list', $phone->alertList, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':fraud_detected', $phone->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }

    public function insertEnrichedInvalid(PhoneInvalidEntity $phone): int {
        $validationErrors = json_encode([$phone->validationErrors], \JSON_THROW_ON_ERROR);
        $sql = 'INSERT INTO event_phone
                (account_id, key, phone_number, hash, country_code, validation_errors, checked, invalid, fraud_detected, lastseen, created, updated)
            VALUES
                (:account_id, :key, :phone_number, :hash, :country_code, :validation_errors, :checked, :invalid, :fraud_detected, :lastseen, :created, :updated)
            ON CONFLICT (key, account_id, phone_number) DO UPDATE
            SET
                hash = EXCLUDED.hash, country_code = EXCLUDED.country_code, validation_errors = EXCLUDED.validation_errors,
                checked = EXCLUDED.checked, invalid = EXCLUDED.invalid, fraud_detected = EXCLUDED.fraud_detected, lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $phone->accountId);
        $stmt->bindValue(':key', $phone->apiKeyId);
        $stmt->bindValue(':phone_number', $phone->phoneNumber);
        $stmt->bindValue(':hash', $phone->hash);
        $stmt->bindValue(':country_code', $phone->countryId);
        $stmt->bindValue(':validation_errors', $validationErrors);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':invalid', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':fraud_detected', $phone->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $phone->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }
}
