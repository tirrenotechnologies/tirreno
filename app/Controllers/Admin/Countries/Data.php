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

namespace Controllers\Admin\Countries;

class Data extends \Controllers\Base {
    public function getList(int $apiKey): array {
        $result = [];

        $model = new \Models\Grid\Countries\Grid($apiKey);

        $result = $model->getAllCountries();

        $ids = array_column($result['data'], 'id');
        if ($ids) {
            $model = new \Models\Country();
            $model->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = $model->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getMap(int $apiKey): array {
        $result = [];

        $model = new \Models\Map();

        $ispId = $this->f3->get('REQUEST.ispId');
        $userId = $this->f3->get('REQUEST.userId');
        $botId = $this->f3->get('REQUEST.botId');
        $domainId = $this->f3->get('REQUEST.domainId');
        $resourceId = $this->f3->get('REQUEST.resourceId');

        if (isset($userId) && is_numeric($userId)) {
            $result = $model->getCountriesByUserId($userId, $apiKey);
        }

        if (isset($ispId) && is_numeric($ispId)) {
            $result = $model->getCountriesByIspId($ispId, $apiKey);
        }

        if (isset($domainId) && is_numeric($domainId)) {
            $result = $model->getCountriesByDomainId($domainId, $apiKey);
        }

        if (isset($botId) && is_numeric($botId)) {
            $result = $model->getCountriesByBotId($botId, $apiKey);
        }

        if (isset($resourceId) && is_numeric($resourceId)) {
            $result = $model->getCountriesByResourceId($resourceId, $apiKey);
        }

        if (!$result) {
            $dateFrom = $this->f3->get('REQUEST.dateFrom');
            $dateTo = $this->f3->get('REQUEST.dateTo');

            $dateFrom = ($dateFrom) ? date('Y-m-d H:i:s', strtotime($dateFrom)) : null;
            $dateTo = ($dateTo) ? date('Y-m-d H:i:s', strtotime($dateTo)) : null;

            $result = $model->getAllCountries($dateFrom, $dateTo, $apiKey);
        }

        return $result;
    }
}
