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

class Home extends \Tirreno\Controllers\Services\Base {
    public function getStat(string $mode, ?array $dateRange, int $apiKey): array {
        $result = [
            'total'         => null,
            'allTimeTotal'  => null,
        ];

        // NOTE: removed allTimeTotal key
        switch ($mode) {
            case 'totalEvents':
                $result['total'] = tirreno('models')->dashboard->getTotalEvents($dateRange, $apiKey);
                break;
            case 'totalUsers':
                $result['total'] = tirreno('models')->dashboard->getTotalUsers($dateRange, $apiKey);
                break;
            case 'totalIps':
                $result['total'] = tirreno('models')->dashboard->getTotalIps($dateRange, $apiKey);
                break;
            case 'totalCountries':
                $result['total'] = tirreno('models')->dashboard->getTotalCountries($dateRange, $apiKey);
                break;
            case 'totalUrls':
                $result['total'] = tirreno('models')->dashboard->getTotalResources($dateRange, $apiKey);
                break;
            case 'totalUsersForReview':
                $result['total'] = tirreno('models')->dashboard->getTotalUsersForReview($dateRange, $apiKey);
                break;
            case 'totalBlockedUsers':
                $result['total'] = tirreno('models')->dashboard->getTotalBlockedUsers($dateRange, $apiKey);
                break;
        }

        return $result;
    }

    public function getTopTen(string $mode, ?array $dateRange, int $apiKey): array {
        $modelMap = tirreno('utils')->constants->TOP_TEN_MODELS_MAP;

        $model = array_key_exists($mode, $modelMap) ? new $modelMap[$mode]() : null;
        $data = $model ? $model->getList($apiKey, $dateRange) : [];
        $total = count($data);

        return [
            'draw'              => tirreno('request')->getRequestParam('draw') ?? 1,
            'recordsTotal'      => $total,
            'recordsFiltered'   => $total,
            'data'              => $data,
        ];
    }
}
