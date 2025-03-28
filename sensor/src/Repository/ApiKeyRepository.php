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

use Sensor\Dto\GetApiKeyDto;
use Sensor\Type\SkippedEnrichingAttributeType;

class ApiKeyRepository {
    /** @var array<string, GetApiKeyDto|null> */
    private array $cache = [];

    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function getApiKey(string $apiKey): ?GetApiKeyDto {
        if (array_key_exists($apiKey, $this->cache)) {
            return $this->cache[$apiKey];
        }

        $sql = 'SELECT id, key, token, skip_blacklist_sync, skip_enriching_attributes FROM dshb_api WHERE key = :api_key LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':api_key', $apiKey);
        $stmt->execute();

        /** @var array{id: int, key: string, token: string, skip_blacklist_sync: bool, skip_enriching_attributes: string}|false $result */
        $result = $stmt->fetch();

        if ($result === false) {
            $this->cache[$apiKey] = null;

            return null;
        }

        $skipEnrichingAttributes = (array) \json_decode($result['skip_enriching_attributes'], true);

        $this->cache[$apiKey] = new GetApiKeyDto(
            (int) $result['id'],
            $result['key'],
            $result['token'],
            !$result['skip_blacklist_sync'],
            \in_array(SkippedEnrichingAttributeType::Domain, $skipEnrichingAttributes, true),
            \in_array(SkippedEnrichingAttributeType::Email, $skipEnrichingAttributes, true),
            \in_array(SkippedEnrichingAttributeType::Ip, $skipEnrichingAttributes, true),
            \in_array(SkippedEnrichingAttributeType::UserAgent, $skipEnrichingAttributes, true),
            \in_array(SkippedEnrichingAttributeType::Phone, $skipEnrichingAttributes, true),
        );

        return $this->cache[$apiKey];
    }

    public function updateApiCallReached(?bool $success, GetApiKeyDto $dto): void {
        if ($success === null) {
            return;
        }
        $sql = 'UPDATE dshb_api SET last_call_reached = :success WHERE id = :id AND (last_call_reached IS NULL OR last_call_reached != :success)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $dto->id);
        $stmt->bindValue(':success', $success, \PDO::PARAM_BOOL);
        $stmt->execute();
    }
}
