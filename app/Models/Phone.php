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

namespace Models;

class Phone extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_phone';

    public function getPhoneDetails(int $id, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $id,
        ];

        $query = (
            'SELECT
                event_phone.id,
                event_phone.account_id,
                event_phone.phone_number,
                event_phone.national_format,
                event_phone.country_code,
                -- event_phone.validation_errors,
                -- event_phone.mobile_country_code,
                -- event_phone.mobile_network_code,
                event_phone.carrier_name,
                event_phone.type,
                event_phone.lastseen,
                event_phone.created,
                event_phone.shared,
                event_phone.fraud_detected,
                -- event_phone.alert_list,
                -- event_phone.profiles,
                event_phone.iso_country_code,
                event_phone.invalid,
                event_phone.checked,
                countries.serial              AS phone_serial,
                countries.id                  AS phone_country,
                countries.value               AS phone_full_country

            FROM
                event_phone
            LEFT JOIN countries
            ON (countries.serial = event_phone.country_code)

            WHERE
                event_phone.id = :id AND
                event_phone.key = :api_key'
        );

        $result = $this->execQuery($query, $params);

        if (count($result)) {
            $result = $result[0];
            $result['shared_users'] = [];

            if ($result['shared'] > 1) {
                $params = [
                    ':api_key' => $apiKey,
                    ':phone_number' => $result['phone_number'],
                    ':current_account' => $result['account_id'],
                ];
                $query = (
                    'SELECT
                        event_account.score,
                        event_account.score_updated_at,
                        event_account.id     AS accountid,
                        event_account.userid AS accounttitle,
                        event_email.email

                    FROM event_account

                    LEFT JOIN event_email
                    ON event_account.lastemail = event_email.id

                    WHERE event_account.id IN (
                        SELECT event_phone.account_id
                        FROM event_phone
                        WHERE
                            event_phone.phone_number = :phone_number AND
                            event_phone.key = :api_key AND
                            event_phone.account_id != :current_account
                    ) AND
                    event_account.key = :api_key'
                );
                $result['shared_users'] = $this->execQuery($query, $params);
            }
        }

        return $result;
    }

    public function getIdByValue(string $phone, int $apiKey): ?int {
        $query = (
            'SELECT
                event_phone.id
            FROM
                event_phone
            WHERE
                event_phone.key = :api_key
                AND event_phone.phone_number = :phone_value'
        );

        $params = [
            ':phone_value' => $phone,
            ':api_key' => $apiKey,
        ];

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function updateFraudFlag(array $ids, bool $fraud, int $apiKey): void {
        if (!count($ids)) {
            return;
        }

        [$params, $placeHolders] = $this->getArrayPlaceholders($ids);

        $params[':fraud'] = $fraud;
        $params[':api_key'] = $apiKey;

        $query = (
            "UPDATE event_phone
                SET fraud_detected = :fraud

            WHERE
                key = :api_key
                AND id IN ({$placeHolders})"
        );

        $this->execQuery($query, $params);
    }

    // phone_number may be null for some events
    public function updateTotalsByValues(array $values, int $apiKey): void {
        foreach ($values as $value) {
            if ($value !== null) {
                $this->updateTotals($value, $apiKey);
            }
        }
    }

    public function updateTotals(string $phoneNumber, int $apiKey): void {
        $params = [
            ':phone_number' => $phoneNumber,
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(*) AS cnt
            FROM
                event_phone
            WHERE
                event_phone.key = :key AND
                event_phone.phone_number = :phone_number'
        );
        $results = $this->execQuery($query, $params);

        $params[':cnt'] = $results[0]['cnt'];

        $query = (
            'UPDATE event_phone
            SET
                shared = :cnt
            WHERE
                event_phone.key = :key AND
                event_phone.phone_number = :phone_number'
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
                COALESCE(event_phone.phone_number, '') AS value,
                event_phone.hash AS hash

            FROM
                event_phone

            WHERE
                event_phone.key = :api_key
                AND event_phone.id = :id

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function updateTotalsByAccountIds(array $ids, int $apiKey): int {
        if (!count($ids)) {
            return 0;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;

        $idsQuery = (
            "SELECT
                DISTINCT event_phone.phone_number
            FROM event_phone
            WHERE
                event_phone.account_id IN ($flatIds) AND
                event_phone.key = :key"
        );

        $query = (
            "UPDATE event_phone
            SET
                shared = COALESCE(sub.shared, 1)
            FROM (
                SELECT
                    COUNT(*) AS shared,
                    event_phone.phone_number
                FROM
                    event_phone
                WHERE
                    event_phone.phone_number IN ($idsQuery) AND
                    event_phone.key = :key
                GROUP BY event_phone.phone_number
            ) AS sub
            RIGHT JOIN event_phone sub_phone ON sub.phone_number = sub_phone.phone_number
            WHERE
                event_phone.phone_number = sub_phone.phone_number AND
                event_phone.phone_number IN ($idsQuery) AND
                event_phone.key = :key"
        );

        return $this->execQuery($query, $params);
    }

    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $extraClause = $force ? '' : ' AND event_phone.lastseen >= event_phone.updated';

        $query = (
            "UPDATE event_phone
            SET
                shared = (
                    SELECT COUNT(*)
                    FROM event_phone AS ep
                    WHERE
                        ep.phone_number = event_phone.phone_number AND
                        ep.key = :key
                ),
                updated = date_trunc('milliseconds', now())
            WHERE
                event_phone.id IN ({$flatIds}) AND
                event_phone.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                shared
            FROM event_phone
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['shared'] = $indexedResult[$item['id']]['shared'];
            $res[$idx] = $item;
        }

        return $res;
    }

    public function countNotChecked(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(*) AS count
            FROM event_phone
            WHERE
                event_phone.key = :key AND
                event_phone.checked IS FALSE'
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
            FROM event_phone
            WHERE
                event_phone.key = :key AND
                event_phone.checked IS FALSE
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
                event_phone.id
            FROM event_phone
            WHERE
                event_phone.account_id = :user_id AND
                event_phone.key = :api_key AND
                event_phone.checked IS FALSE'
        );

        return array_column($this->execQuery($query, $params), 'id');
    }
}
