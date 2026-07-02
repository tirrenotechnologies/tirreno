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

namespace Tirreno\Controllers\Services;

class FieldAudits extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        $result = tirreno('grids')->fieldAudits->getAll($apiKey);

        $ids = array_column($result['data'], 'field_audit_id');
        if ($ids && tirreno('utils')->variables->getRecalculateTotalsOnVisit()) {
            tirreno('models')->fieldAudit->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = tirreno('models')->fieldAudit->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getChart(int $apiKey): array {
        return tirreno('charts')->fields->getData($apiKey);
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        return [
            'SUCCESS_MESSAGE'   => tirreno('storage')->get('totals_success_message'),
            'totals'            => tirreno('models')->fieldAudit->getTimeFrameTotal($ids, $startDate, $endDate, $apiKey),
        ];
    }

    public function getTrailList(int $apiKey): array {
        $map = [
            'userId'        => 'getDataByUserId',
            'resourceId'    => 'getDataByResourceId',
            'fieldId'       => 'getDataByFieldId',
        ];

        $result = $this->idMapIterate($map, tirreno('grids')->fieldAuditTrail, $apiKey);

        $ids = array_column($result['data'], 'field_audit_id');
        if ($ids && tirreno('utils')->variables->getRecalculateTotalsOnVisit()) {
            tirreno('models')->fieldAudit->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = tirreno('models')->fieldAudit->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getFieldEventDetails(int $id, int $apiKey): array {
        $result = [];
        $trailResult = tirreno('models')->fieldAuditTrail->getById($id, $apiKey);

        if ($trailResult) {
            $eventId = $trailResult['event_id'];
            $result = tirreno('controllers')->events->getEventDetails($eventId, $apiKey);

            if ($result) {
                $result = tirreno('controllers')->events->extendPayload($result, $apiKey);
            }
        }

        return $result;
    }
}
