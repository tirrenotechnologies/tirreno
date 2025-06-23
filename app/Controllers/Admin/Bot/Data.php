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

namespace Controllers\Admin\Bot;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function proceedPostRequest(array $params): array {
        return match ($params['cmd']) {
            'reenrichment' => $this->enrichEntity($params),
            default => []
        };
    }

    public function enrichEntity(array $params): array {
        $dataController = new \Controllers\Admin\Enrichment\Data();
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $enrichmentKey = $this->getCurrentOperatorEnrichmentKeyString();
        $type = $params['type'];
        $search = $params['search'] ?? null;
        $entityId = isset($params['entityId']) ? (int) $params['entityId'] : null;

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function checkIfOperatorHasAccess(int $botId): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Bot();

        return $model->checkAccess($botId, $apiKey);
    }

    public function getBotDetails(int $botId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Bot();

        return $model->getFullBotInfoById($botId, $apiKey);
    }

    public function isEnrichable(): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\ApiKeys();

        return $model->attributeIsEnrichable('ua', $apiKey);
    }
}
