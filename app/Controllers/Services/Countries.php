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

class Countries extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        $result = tirreno('grids')->countries->getAll($apiKey);

        $ids = array_column($result['data'], 'id');
        if ($ids && tirreno('utils')->variables->getRecalculateTotalsOnVisit()) {
            tirreno('models')->country->updateTotalsByEntityIds($ids, $apiKey);
            $result['data'] = tirreno('models')->country->refreshTotals($result['data'], $apiKey);
        }

        return $result;
    }

    public function getMap(int $apiKey): array {
        $map = [
            'userId'        => 'getCountriesByUserId',
            'ispId'         => 'getCountriesByIspId',
            'userAgentId'   => 'getCountriesByUserAgentId',
            'domainId'      => 'getCountriesByDomainId',
            'resourceId'    => 'getCountriesByResourceId',
        ];

        $result = $this->idMapIterate($map, tirreno('models')->map, $apiKey, 'getAllCountriesByDateRange');

        return $result;
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        return [
            'SUCCESS_MESSAGE'   => tirreno('storage')->get('totals_success_message'),
            'totals'            => tirreno('models')->country->getTimeFrameTotal($ids, $startDate, $endDate, $apiKey),
        ];
    }
}
