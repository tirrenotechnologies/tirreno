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

namespace Controllers\Admin\Home;

class Data extends \Controllers\Base {
    use \Traits\DateRange;

    public function getChart(int $apiKey): array {
        $request = $this->f3->get('REQUEST');
        $type = $request['type'];
        $mode = $request['mode'];
        $modelMap = \Utils\Constants::get('CHART_MODEL_MAP');

        $model = array_key_exists($mode, $modelMap) ? new $modelMap[$mode]() : null;

        return $model ? $model->getData($apiKey) : [[], []];
    }

    public function getStat(int $apiKey): array {
        $request = $this->f3->get('REQUEST');
        $dateRange = $this->getDatesRange($request);
        $mode = $request['mode'];

        $model = new \Models\Dashboard();

        $result = [
            'total'         => null,
            'allTimeTotal'  => null,
        ];

        switch ($mode) {
            case 'totalEvents':
                $result['total']        = $model->getTotalEvents($dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalEvents(null, $apiKey);
                break;
            case 'totalUsers':
                $result['total']        = $model->getTotalUsers($dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalUsers(null, $apiKey);
                break;
            case 'totalIps':
                $result['total']        = $model->getTotalIps($dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalIps(null, $apiKey);
                break;
            case 'totalCountries':
                $result['total']        = $model->getTotalCountries($dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalCountries(null, $apiKey);
                break;
            case 'totalUrls':
                $result['total']        = $model->getTotalResources($dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalResources(null, $apiKey);
                break;
            case 'totalUsersForReview':
                $keyModel = new \Models\ApiKeys();
                $keyModel->getKeyById($apiKey);
                $reviewQueueThreshold = $keyModel->review_queue_threshold;

                $result['total']        = $model->getTotalUsersForReview($reviewQueueThreshold, $dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalUsersForReview($reviewQueueThreshold, null, $apiKey);
                break;
            case 'totalBlockedUsers':
                $result['total']        = $model->getTotalBlockedUsers($dateRange, $apiKey);
                $result['allTimeTotal'] = $model->getTotalBlockedUsers(null, $apiKey);
                break;
        }

        return $result;
    }

    public function getTopTen(int $apiKey): array {
        $params = $this->f3->get('GET');
        $dateRange = $this->getDatesRange($params);
        $mode = $params['mode'];
        $modelMap = \Utils\Constants::get('TOP_TEN_MODELS_MAP');

        $model = array_key_exists($mode, $modelMap) ? new $modelMap[$mode]() : null;
        $data = $model ? $model->getList($apiKey, $dateRange) : [];
        $total = count($data);

        return [
            'draw' => $params['draw'] ?? 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ];
    }
}
