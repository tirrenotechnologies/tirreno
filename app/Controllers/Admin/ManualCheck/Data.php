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

namespace Tirreno\Controllers\Admin\ManualCheck;

class Data extends \Tirreno\Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return $this->performSearch();
    }

    public function performSearch(): array {
        $params = $this->extractRequestParams(['token', 'search', 'type']);

        $pageParams = [
            'SEARCH_VALUES' => $params,
        ];

        $apiKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();
        $errorCode = \Tirreno\Utils\Validators::validateSearch($params, $enrichmentKey);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;

            return $pageParams;
        }

        $type   = \Tirreno\Utils\Conversion::getStringRequestParam('type');
        $search = \Tirreno\Utils\Conversion::getStringRequestParam('search');

        $controller = new \Tirreno\Controllers\Admin\Enrichment\Data();
        $result = $controller->enrichEntity($type, $search, null, $apiKey, $enrichmentKey);

        if (isset($result['ERROR_CODE'])) {
            $pageParams['ERROR_CODE'] = $result['ERROR_CODE'];

            return $pageParams;
        }

        $operatorId = \Tirreno\Utils\Routes::getCurrentRequestOperator()->id;
        $this->saveSearch($search, $type, $operatorId);

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

    private function saveSearch(string $query, string $type, int $operatorId): void {
        $history = new \Tirreno\Models\ManualCheckHistory();
        $history->insertRecord($query, $type, $operatorId);
    }

    public function getSearchHistory(int $operatorId): ?array {
        $model = new \Tirreno\Models\ManualCheckHistory();

        return $model->getLastByOperatorId($operatorId);
    }
}
