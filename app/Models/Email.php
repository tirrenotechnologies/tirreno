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

namespace Models;

class Email extends \Models\BaseSql implements \Interfaces\FraudFlagUpdaterInterface {
    protected $DB_TABLE_NAME = 'event';

    public function getEmailDetails(int $id, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $id,
        ];

        $query = (
            'SELECT
                event_email.id AS email_id,
                event_email.email,
                event_email.lastseen AS email_lastseen,
                event_email.created AS email_created,
                event_email.data_breach,
                event_email.data_breaches,
                event_email.earliest_breach,
                event_email.profiles,
                event_email.blockemails,
                event_email.domain_contact_email,
                event_email.fraud_detected,
                event_email.checked,
                -- event_email.alert_list,

                event_domain.id AS domain_id,
                event_domain.domain,
                event_domain.blockdomains,
                event_domain.disposable_domains,
                event_domain.total_visit,
                event_domain.total_account,
                event_domain.lastseen AS domain_lastseen,
                event_domain.created AS domain_created,
                event_domain.free_email_provider,
                event_domain.tranco_rank,
                event_domain.creation_date,
                event_domain.expiration_date,
                event_domain.return_code,
                event_domain.closest_snapshot,
                event_domain.mx_record,
                event_domain.disabled

            FROM
                event_email
            LEFT JOIN event_domain
            ON event_email.domain = event_domain.id

            WHERE
                event_email.id = :id AND
                event_email.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        \Utils\Enrichment::calculateEmailReputation($results);

        return $results[0] ?? [];
    }

    public function getIdByValue(string $email, int $apiKey): ?int {
        $query = (
            'SELECT
                event_email.id
            FROM
                event_email
            WHERE
                event_email.email = :email_value AND
                event_email.key = :api_key'
        );

        $params = [
            ':email_value' => $email,
            ':api_key' => $apiKey,
        ];

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function getSeenInLastDay(
        bool $includeAlertListed = false,
        bool $includeWithoutHash = false,
        bool $includeWithBlacklistSyncSkipped = false,
    ): array {
        $params = [
            ':includeAlertListed' => $includeAlertListed,
            ':includeWithoutHash' => $includeWithoutHash,
            ':includeWithBlacklistSyncSkipped' => $includeWithBlacklistSyncSkipped,
        ];

        $query = (
            'SELECT
                event_email.key,
                event_email.email,
                event_email.hash
            FROM
                event_email
            JOIN
                event_account ON event_email.account_id = event_account.id
            JOIN
                dshb_api ON event_account.key = dshb_api.id
            WHERE
                event_email.lastseen >= CURRENT_DATE - 1 AND
                (:includeAlertListed = TRUE OR event_email.alert_list != TRUE OR event_email.alert_list IS NULL) AND
                (:includeWithoutHash = TRUE OR event_email.hash IS NOT NULL) AND
                (:includeWithBlacklistSyncSkipped = TRUE OR dshb_api.skip_blacklist_sync != TRUE)'
        );

        return $this->execQuery($query, $params);
    }

    public function updateAlertListedByHashes(array $hashes, bool $alertListed, int $apiKey): void {
        [$params, $placeHolders] = $this->getArrayPlaceholders($hashes);
        $params[':alertListed'] = $alertListed;
        $params[':key'] = $apiKey;

        $query = (
            "UPDATE event_email
                SET alert_list = :alertListed
            WHERE
                key = :key AND
                hash IN ({$placeHolders})"
        );

        $this->execQuery($query, $params);
    }

    public function updateFraudFlag(array $ids, bool $fraud, int $apiKey): void {
        if (!count($ids)) {
            return;
        }

        [$params, $placeHolders] = $this->getArrayPlaceholders($ids);

        $params[':fraud'] = $fraud;
        $params[':api_key'] = $apiKey;

        $query = (
            "UPDATE event_email
                SET fraud_detected = :fraud

            WHERE
                id IN ({$placeHolders}) AND
                key = :api_key"
        );

        $this->execQuery($query, $params);
    }

    public function extractById(int $entityId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $entityId,
        ];

        $query = (
            "SELECT
                COALESCE(event_email.email, '') AS value,
                event_email.hash AS hash

            FROM
                event_email

            WHERE
                event_email.id = :id AND
                event_email.key = :api_key

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function countNotChecked(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(*) AS count
            FROM event_email
            WHERE
                event_email.key = :key AND
                event_email.checked IS FALSE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedExists(int $apiKey): bool {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT 1
            FROM event_email
            WHERE
                event_email.key = :key AND
                event_email.checked IS FALSE
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return (bool) count($results);
    }

    public function notCheckedForUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];

        $query = (
            'SELECT DISTINCT
                event_email.id
            FROM event_email
            WHERE
                event_email.account_id = :user_id AND
                event_email.key = :api_key AND
                event_email.checked IS FALSE'
        );

        return array_column($this->execQuery($query, $params), 'id');
    }
}
