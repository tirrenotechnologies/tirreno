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

namespace Controllers\Admin\IP;

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
        $subscriptionKeyString = $this->getCurrentOperatorSubscriptionKeyString();
        $type = $params['type'];
        $search = $params['search'] ?? null;
        $entityId = isset($params['entityId']) ? (int) $params['entityId'] : null;

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $subscriptionKeyString);
    }

    public function checkIfOperatorHasAccess(int $ipId): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Ip();

        return $model->checkAccess($ipId, $apiKey);
    }

    public function getIpDetails(int $ipId): array {
        $result = $this->getFullIpInfoById($ipId);

        return [
            'abbr_country' => $result['abbr_country'],
            'country' => $result['country'],
            'serial' => $result['serial'],
            'asn' => $result['asn'],
            'blocklist' => $result['blocklist'],
            'fraud_detected' => $result['fraud_detected'],
            'data_center' => $result['data_center'],
            'vpn' => $result['vpn'],
            'tor' => $result['tor'],
            'relay' => $result['relay'],
            'starlink' => $result['starlink'],
            'ispid' => $result['ispid'],
        ];
    }

    public function getFullIpInfoById(int $ipId): array {
        $model = new \Models\Ip();
        $result = $model->getFullIpInfoById($ipId);
        $result['lastseen'] = \Utils\ElapsedDate::short($result['lastseen']);

        return $result;
    }

    public function isEnrichable(): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\ApiKeys();

        return $model->attributeIsEnrichable('ip', $apiKey);
    }
}
