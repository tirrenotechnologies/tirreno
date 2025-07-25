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

namespace Controllers\Admin\Devices;

class Data extends \Controllers\Base {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Models\Grid\Devices\Grid($apiKey);

        $ipId = $this->f3->get('REQUEST.ipId');
        $userId = $this->f3->get('REQUEST.userId');
        $resourceId = $this->f3->get('REQUEST.resourceId');

        if (isset($ipId) && is_numeric($ipId)) {
            $result = $model->getDevicesByIpId($ipId);
        }

        if (isset($userId) && is_numeric($userId)) {
            $result = $model->getDevicesByUserId($userId);
        }

        if (isset($resourceId) && is_numeric($resourceId)) {
            $result = $model->getDevicesByResourceId($resourceId);
        }

        if (!$result) {
            $result = $model->getAllDevices();
        }

        return $result;
    }

    public function getDeviceDetails(int $apiKey): array {
        $params = $this->f3->get('GET');
        $id = $params['id'];
        $model = new \Models\Device();

        $details = $model->getFullDeviceInfoById($id, $apiKey);
        $details['enrichable'] = $this->isEnrichable($apiKey);

        $tsColumns = ['created'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $details);

        return $details;
    }

    private function isEnrichable(int $apiKey): bool {
        $model = new \Models\ApiKeys();

        return $model->attributeIsEnrichable('ua', $apiKey);
    }
}
