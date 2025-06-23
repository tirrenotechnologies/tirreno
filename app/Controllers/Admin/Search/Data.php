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

namespace Controllers\Admin\Search;

class Data extends \Controllers\Base {
    public function getSearchResults(?string $query, int $apiKey): array {
        $result = [];

        if ($query === '' || $query === null) {
            return ['suggestions' => $result];
        }

        $model = new \Models\Search\Domain();
        $result1 = $model->searchByDomain($query, $apiKey);

        $model = new \Models\Search\Ip();
        $result2 = $model->searchByIp($query, $apiKey);

        $model = new \Models\Search\Isp();
        $result3 = $model->searchByIsp($query, $apiKey);

        $model = new \Models\Search\User();
        $result4 = $model->searchByUserId($query, $apiKey);
        $result5 = $model->searchByName($query, $apiKey);

        $model = new \Models\Search\Email();
        $result6 = $model->searchByEmail($query, $apiKey);

        $model = new \Models\Search\Phone();
        $result7 = $model->searchByPhone($query, $apiKey);

        $result = array_merge($result1, $result2, $result3, $result4, $result5, $result6, $result7);
        $iters = count($result);

        for ($i = 0; $i < $iters; ++$i) {
            $result[$i]['data'] = ['category' => $result[$i]['groupName']];
        }

        return [
            'suggestions' => $result,
        ];
    }
}
