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

class Phones extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        $map = [
            'userId' => 'getPhonesByUserId',
        ];

        $result = $this->idMapIterate($map, tirreno('grids')->phones, $apiKey, null);

        $ids = array_column($result['data'], 'id');
        if ($ids && tirreno('utils')->variables->getRecalculateTotalsOnVisit()) {
            tirreno('models')->phone->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = tirreno('models')->phone->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getChart(int $apiKey): array {
        return tirreno('charts')->phones->getData($apiKey);
    }

    public function getPhoneDetails(int $id, int $apiKey): array {
        $details = tirreno('models')->phone->getPhoneDetails($id, $apiKey);
        $details['enrichable'] = $this->isEnrichable($apiKey);

        $tsColumns = ['created', 'lastseen'];
        $details = tirreno('utils')->timezones->localizeTimestampsForActiveOperator($tsColumns, $details);

        return $details;
    }

    private function isEnrichable(int $apiKey): bool {
        return tirreno('models')->apiKeys->attributeIsEnrichable('phone', $apiKey);
    }

    public function enrichEntity(int $entityId, ?string $enrichmentKey, int $apiKey): array {
        if ($enrichmentKey === null) {
            return ['ERROR_CODE' => tirreno('utils')->errorCodes->ENRICHMENT_API_KEY_NOT_EXISTS];
        }
        set_error_handler([\Tirreno\Utils\ErrorHandler::class, 'exceptionErrorHandler']);
        $result = tirreno('controlles')->enrichment->enrichEntityProcess('phone', null, $entityId, $apiKey, $enrichmentKey);
        restore_error_handler();

        return $result;
    }
}
