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

namespace Controllers\Admin\ManualCheck;

class Data extends \Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return $this->performSearch();
    }

    public function performSearch(): array {
        $params = $this->extractRequestParams(['token', 'search', 'type']);

        $pageParams = [
            'SEARCH_VALUES' => $params,
        ];

        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();
        $errorCode = \Utils\Validators::validateSearch($params, $enrichmentKey);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;

            return $pageParams;
        }

        $type   = \Utils\Conversion::getStringRequestParam('type');
        $search = \Utils\Conversion::getStringRequestParam('search');

        $controller = new \Controllers\Admin\Enrichment\Data();
        $result = $controller->enrichEntity($type, $search, null, $apiKey, $enrichmentKey);

        if (isset($result['ERROR_CODE'])) {
            $pageParams['ERROR_CODE'] = $result['ERROR_CODE'];

            return $pageParams;
        }

        $save = [
            'operator'  => \Utils\Routes::getCurrentRequestOperator()->id,
            'type'      => $type,
            'search'    => $search,
        ];

        $this->saveSearch($save);

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

    private function saveSearch(array $params): void {
        $history = new \Models\ManualCheckHistoryQuery();
        $history->add($params);
    }

    public function getSearchHistory(int $operatorId): ?array {
        $model = new \Models\ManualCheckHistory();

        return $model->getRecentByOperator($operatorId);
    }
}
