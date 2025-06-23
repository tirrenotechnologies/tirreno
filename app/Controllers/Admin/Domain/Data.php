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

namespace Controllers\Admin\Domain;

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

    public function checkIfOperatorHasAccess(int $domainId): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Domain();

        return $model->checkAccess($domainId, $apiKey);
    }

    public function getDomainDetails(int $domainId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        $model = new \Models\Domain();
        $result = $model->getFullDomainInfoById($domainId, $apiKey);
        $result['lastseen'] = \Utils\ElapsedDate::short($result['lastseen']);

        return $result;
    }

    public function isEnrichable(): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\ApiKeys();

        return $model->attributeIsEnrichable('domain', $apiKey);
    }
}
