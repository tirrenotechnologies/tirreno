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

namespace Controllers\Admin\Countries;

class Data extends \Controllers\Admin\Base\Data {
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

        $map = [
            'userId'        => 'getCountriesByUserId',
            'ispId'         => 'getCountriesByIspId',
            'botId'         => 'getCountriesByDeviceId',
            'domainId'      => 'getCountriesByDomainId',
            'resourceId'    => 'getCountriesByResourceId',
        ];

        $result = $this->idMapIterate($map, $model, 'getAllCountriesByDateRange', $apiKey);

        return $result;
    }
}
