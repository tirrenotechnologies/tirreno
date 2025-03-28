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

class ApiKeys extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_api';

    public function add(array $data): int {
        $quote = $data['quote'];
        $operatorId = $data['operator_id'];
        $uuid = sprintf('%s%s%s', $operatorId, $quote, time());

        $this->quote = $quote;
        $this->creator = $operatorId;
        $this->key = $this->getHash($uuid);

        if (array_key_exists('skip_enriching_attributes', $data)) {
            $this->skip_enriching_attributes = $data['skip_enriching_attributes'];
        }
        if (array_key_exists('skip_blacklist_sync', $data)) {
            $this->skip_blacklist_sync = $data['skip_blacklist_sync'];
        }

        $this->save();

        return (int) $this->id;
    }

    public function getKeys(int $operatorId): array {
        $filters = [
            'creator=?', $operatorId,
        ];

        return $this->find($filters);
    }

    public function getKey(int $operatorId): ?ApiKeys {
        $keys = $this->getKeys($operatorId);

        return $keys[0] ?? null;
    }

    public function resetKey(int $keyId, int $operatorId): void {
        $this->getByKeyAndOperatorId($keyId, $operatorId);

        if ($this->loaded()) {
            $uuid = sprintf('%s%s%s', $keyId, $operatorId, time());

            $this->key = $this->getHash($uuid);
            $this->save();
        }
    }

    public function getByKeyAndOperatorId(int $keyId, int $operatorId): self|null|false {
        $filters = [
            'id=? AND creator=?', $keyId, $operatorId,
        ];

        return $this->load($filters);
    }

    public function getKeyIdByHash(string $hash): self|null|false {
        $filters = [
            'key=?', $hash,
        ];

        return $this->load($filters);
    }

    public function getKeyById(int $keyId): self|null|false {
        $filters = [
            'id=?', $keyId,
        ];

        return $this->load($filters);
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

        return \json_decode($results[0]['skip_enriching_attributes']);
    }

    public function enrichableAttributes(int $keyId): array {
        $skipAttributes = $this->getSkipEnrichingAttributes($keyId);
        $attributes = \Utils\Constants::get('ENRICHING_ATTRIBUTES');
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

    public function updateSkipEnrichingAttributes(array $attributes): void {
        if ($this->loaded()) {
            $attributes = \array_values($attributes);
            $this->skip_enriching_attributes = \json_encode($attributes);
            $this->save();
        }
    }

    public function updateSkipBlacklistSynchronisation(bool $skip): void {
        if ($this->loaded()) {
            $this->skip_blacklist_sync = $skip;
            $this->save();
        }
    }

    public function updateRetentionPolicy(int $policyInWeeks): void {
        if ($this->loaded()) {
            $this->retention_policy = $policyInWeeks;
            $this->save();
        }
    }

    public function updateBlacklistThreshold(int $value): void {
        if ($this->loaded()) {
            $this->blacklist_threshold = $value;
            $this->save();
        }
    }

    public function updateReviewQueueThreshold(int $value): void {
        if ($this->loaded()) {
            $this->review_queue_threshold = $value;
            $this->save();
        }
    }

    public function updateInternalToken(string $apiToken): void {
        if ($this->loaded()) {
            $this->token = $apiToken;
            $this->save();
        }
    }
}
