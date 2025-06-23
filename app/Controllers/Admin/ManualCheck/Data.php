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

namespace Controllers\Admin\ManualCheck;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function proceedPostRequest(array $params): array {
        return $this->performSearch($params);
    }

    public function performSearch(array $params): array {
        $pageParams = [
            'SEARCH_VALUES' => $params,
        ];

        $apiKey = $this->getCurrentOperatorApiKeyId();
        $enrichmentKey = $this->getCurrentOperatorEnrichmentKeyString();
        $errorCode = $this->validateSearch($params, $enrichmentKey);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;

            return $pageParams;
        }

        $type = $params['type'];

        $controller = new \Controllers\Admin\Enrichment\Data();
        $result = $controller->enrichEntity($type, $params['search'], null, $apiKey, $enrichmentKey);

        if (isset($result['ERROR_CODE'])) {
            $pageParams['ERROR_CODE'] = $result['ERROR_CODE'];

            return $pageParams;
        }

        $this->saveSearch($params);

        // TODO: return alert_list back in next release
        if (array_key_exists('alert_list', $result[$type])) {
            unset($result[$type]['alert_list']);
        }

        if ($type === 'phone') {
            unset($result[$type]['valid']);
            unset($result[$type]['validation_error']);
        }

        if ($type === 'email') {
            unset($result[$type]['data_breaches']);
        }

        $pageParams['RESULT'] = [$type => $result[$type]];

        return $pageParams;
    }

    private function validateSearch(array $params, string $enrichmentKey): bool|int {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $api = \Utils\Variables::getEnrichtmentApi();

        if (!$enrichmentKey || !$api) {
            return \Utils\ErrorCodes::ENRICHMENT_API_KEY_DOES_NOT_EXIST;
        }

        $type = $params['type'] ?? null;
        $types = $this->f3->get('AdminManualCheck_form_types');
        if (!$type || !array_key_exists($type, $types)) {
            return \Utils\ErrorCodes::TYPE_DOES_NOT_EXIST;
        }

        $search = $params['search'] ?? null;
        if (!$search || strlen($search) < 1) {
            return \Utils\ErrorCodes::SEARCH_QUERY_DOES_NOT_EXIST;
        }

        return false;
    }

    private function saveSearch(array $params): void {
        $history = new \Models\ManualCheckHistoryQuery();
        $history->add($params);
    }

    public function getSearchHistory(int $operatorId): ?array {
        $model = new \Models\ManualCheckHistory();

        return $model->getRecentByOperator($operatorId);
    }
}
