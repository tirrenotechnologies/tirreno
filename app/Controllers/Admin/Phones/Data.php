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

namespace Controllers\Admin\Phones;

class Data extends \Controllers\Base {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Models\Grid\Phones\Grid($apiKey);

        $userId = $this->f3->get('REQUEST.userId');

        if (isset($userId) && is_numeric($userId)) {
            $result = $model->getPhonesByUserId($userId);
        }

        $ids = array_column($result['data'], 'id');
        if ($ids) {
            $model = new \Models\Phone();
            $model->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = $model->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getPhoneDetails(int $apiKey): array {
        $params = $this->f3->get('GET');
        $id = $params['id'];
        $model = new \Models\Phone();

        $details = $model->getPhoneDetails($id, $apiKey);
        $details['enrichable'] = $this->isEnrichable($apiKey);

        $tsColumns = ['created', 'lastseen'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $details);

        return $details;
    }

    private function isEnrichable(int $apiKey): bool {
        $model = new \Models\ApiKeys();

        return $model->attributeIsEnrichable('phone', $apiKey);
    }
}
