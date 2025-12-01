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

namespace Controllers\Admin\FieldAuditTrail;

class Data extends \Controllers\Admin\Base\Data {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Models\Grid\FieldAuditTrail\Grid($apiKey);

        $map = [
            'userId'        => 'getDataByUserId',
            'resourceId'    => 'getDataByResourceId',
            'fieldId'       => 'getDataByFieldId',
        ];

        $result = $this->idMapIterate($map, $model, 'getAllData');

        $ids = array_column($result['data'], 'field_audit_id');
        if ($ids) {
            $model = new \Models\FieldAudit();
            $model->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = $model->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getFieldEventDetails(int $id, int $apiKey): array {
        $result = [];
        $model = new \Models\FieldAuditTrail();
        $trailResult = $model->getById($id, $apiKey);

        if ($trailResult) {
            $eventId = $trailResult['event_id'];
            $controller = new \Controllers\Admin\Events\Data();
            $result = $controller->getEventDetails($eventId, $apiKey);

            if ($result) {
                $result = $controller->extendPayload($result, $apiKey);
            }
        }

        return $result;
    }
}
