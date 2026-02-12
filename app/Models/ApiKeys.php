<?php

/**
 * tirreno ~ open-source security framework
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

namespace Tirreno\Models;

class ApiKeys extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_api';

    public function insertRecord(string $skipEnrichingAttr, bool $skipBlacklistSync, int $operatorId): int {
        $quote = $this->f3->get('DEFAULT_API_KEY_QUOTE');
        $uuid = sprintf('%s%s%s', $operatorId, $quote, time());

        $params = [
            ':quote'                => $this->f3->get('DEFAULT_API_KEY_QUOTE'),
            ':operator_id'          => $operatorId,
            ':skip_enriching_attr'  => $skipEnrichingAttr,
            ':skip_blacklist_sync'  => $skipBlacklistSync,
            ':key'                  => \Tirreno\Utils\Access::saltHash($uuid),
        ];

        $query = (
            'INSERT INTO dshb_api (
                quote, creator, key, skip_enriching_attributes, skip_blacklist_sync
            ) VALUES (
                :quote, :operator_id, :key, :skip_enriching_attr, :skip_blacklist_sync
            ) RETURNING id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'];
    }

    public function getKeys(int $operatorId): array {
        $params = [
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'SELECT
                id,
                key,
                token,
                creator,
                created_at,
                retention_policy,
                last_call_reached,
                skip_blacklist_sync,
                review_queue_threshold,
                skip_enriching_attributes,
                blacklist_threshold
            FROM
                dshb_api
            WHERE
                dshb_api.creator = :operator_id
            ORDER BY id ASC'
        );

        return $this->execQuery($query, $params);
    }

    public function getKey(int $operatorId): ?ApiKeys {
        $keys = $this->getKeys($operatorId);

        return $keys[0] ?? null;
    }

    public function resetKey(int $keyId, int $operatorId): void {
        $uuid = sprintf('%s%s%s', $keyId, $operatorId, time());

        $params = [
            ':operator_id'  => $operatorId,
            ':key_id'       => $keyId,
            ':key'          => \Tirreno\Utils\Access::saltHash($uuid),
        ];

        $query = (
            'UPDATE dshb_api
            SET
                key = :key
            WHERE
                dshb_api.id = :key_id AND
                dshb_api.creator = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function existsByKeyAndOperatorId(int $keyId, int $operatorId): bool {
        $params = [
            ':operator_id'  => $operatorId,
            ':key_id'       => $keyId,
        ];

        $query = (
            'SELECT 1
            FROM
                dshb_api
            WHERE
                dshb_api.creator = :operator_id AND
                dshb_api.id = :key_id'
        );

        return boolval(count($this->execQuery($query, $params)));
    }

    public function getKeyIdByHash(string $hash): ?int {
        $params = [
            ':key'  => $hash,
        ];

        $query = (
            'SELECT
                id
            FROM
                dshb_api
            WHERE
                dshb_api.key = :key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function getKeyById(int $keyId): ?array {
        $params = [
            ':id'   => $keyId,
        ];

        $query = (
            'SELECT
                id,
                key,
                token,
                creator,
                created_at,
                retention_policy,
                last_call_reached,
                skip_blacklist_sync,
                review_queue_threshold,
                skip_enriching_attributes,
                blacklist_threshold
            FROM
                dshb_api
            WHERE dshb_api.id = :id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? null;
    }

    public function getTimezoneByKeyId(int $keyId): string {
        $params = [
            ':api_key' => $keyId,
        ];

        $query = (
            'SELECT
                dshb_operators.timezone
            FROM
                dshb_api
            JOIN dshb_operators
            ON dshb_operators.id = dshb_api.creator
            WHERE
                dshb_api.id = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['timezone'] ?? 'UTC';
    }

    public function getSkipEnrichingAttributes(int $keyId): array {
        $params = [
            ':api_key' => $keyId,
        ];

        $query = (
            'SELECT
                dshb_api.skip_enriching_attributes
            FROM dshb_api
            WHERE
                dshb_api.id = :api_key'
        );

        $results = $this->execQuery($query, $params);

        if (!count($results)) {
            return [];
        }

        $results = json_decode($results[0]['skip_enriching_attributes']);

        if (!\Tirreno\Utils\Variables::getEmailPhoneAllowed()) {
            if (!in_array('email', $results, true)) {
                $results[] = 'email';
            }
            if (!in_array('phone', $results, true)) {
                $results[] = 'phone';
            }
            if (!in_array('domain', $results, true)) {
                $results[] = 'domain';
            }
        }

        return $results;
    }

    public function enrichableAttributes(int $keyId): array {
        $skipAttributes = $this->getSkipEnrichingAttributes($keyId);
        $attributes = \Tirreno\Utils\Constants::get()->ENRICHING_ATTRIBUTES;
        $attributes = array_diff_key($attributes, array_flip($skipAttributes));

        return $attributes;
    }

    public function attributeIsEnrichable(string $attr, int $keyId): bool {
        return array_key_exists($attr, $this->enrichableAttributes($keyId));
    }

    public function getAllApiKeyIds(): array {
        $query = 'SELECT id from dshb_api';
        return $this->execQuery($query, null);
    }

    public function updateSkipEnrichingAttributes(array $attributes, int $keyId): void {
        $params = [
            ':value'    => json_encode(array_values($attributes)),
            ':id'       => $keyId,
        ];

        $query = (
            'UPDATE dshb_api
            SET
                skip_enriching_attributes = :value
            WHERE
                dshb_api.id = :id'
        );

        $this->execQuery($query, $params);
    }

    public function updateSkipBlacklistSynchronisation(bool $skip, int $keyId): void {
        $params = [
            ':value'    => $skip,
            ':id'       => $keyId,
        ];

        $query = (
            'UPDATE dshb_api
            SET
                skip_blacklist_sync = :value
            WHERE
                dshb_api.id = :id'
        );

        $this->execQuery($query, $params);
    }

    public function updateRetentionPolicy(int $policyInWeeks, int $keyId): void {
        $params = [
            ':value'    => $policyInWeeks,
            ':id'       => $keyId,
        ];

        $query = (
            'UPDATE dshb_api
            SET
                retention_policy = :value
            WHERE
                dshb_api.id = :id'
        );

        $this->execQuery($query, $params);
    }

    public function updateBlacklistThreshold(int $value, int $keyId): void {
        $params = [
            ':value'    => $value,
            ':id'       => $keyId,
        ];

        $query = (
            'UPDATE dshb_api
            SET
                blacklist_threshold = :value
            WHERE
                dshb_api.id = :id'
        );

        $this->execQuery($query, $params);
    }

    public function updateReviewQueueThreshold(int $value, int $keyId): void {
        $params = [
            ':value'    => $value,
            ':id'       => $keyId,
        ];

        $query = (
            'UPDATE dshb_api
            SET
                review_queue_threshold = :value
            WHERE
                dshb_api.id = :id'
        );

        $this->execQuery($query, $params);
    }

    public function updateInternalToken(string $apiToken, int $keyId): void {
        $params = [
            ':value'    => $apiToken,
            ':id'       => $keyId,
        ];

        $query = (
            'UPDATE dshb_api
            SET
                token = :value
            WHERE
                dshb_api.id = :id'
        );

        $this->execQuery($query, $params);
    }
}
