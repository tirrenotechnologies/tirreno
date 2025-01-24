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
        $modelMap = \Utils\Constants::CHART_MODEL_MAP;

        $model = array_key_exists($mode, $modelMap) ? new $modelMap[$mode]() : null;

        return $model ? $model->getData($apiKey) : [[], []];
    }

    public function getStat(int $apiKey): array {
        $model = new \Models\Dashboard();

        $statByPeriod = $model->getStatWithDateRange($apiKey);
        $allTimeStat = $model->getStatWithoutDateRange($apiKey);

        return [
            'events' => $statByPeriod['events'],
            'eventsAllTime' => $allTimeStat['events'],

            'users' => $statByPeriod['users'],
            'usersAllTime' => $allTimeStat['users'],

            'ips' => $statByPeriod['ips'],
            'ipsAllTime' => $allTimeStat['ips'],

            'countries' => $statByPeriod['countries'],
            'countriesAllTime' => $allTimeStat['countries'],

            'resources' => $statByPeriod['resources'],
            'resourcesAllTime' => $allTimeStat['resources'],

            'blockedUsers' => $statByPeriod['blockedUsers'],
            'blockedUsersAllTime' => $allTimeStat['blockedUsers'],

            'usersForReview' => $statByPeriod['usersForReview'],
            'usersForReviewAllTime' => $allTimeStat['usersForReview'],
        ];
    }

    public function getTopTen(int $apiKey): array {
        $params = $this->f3->get('GET');
        $dateRange = $this->getDatesRange($params);
        $mode = $params['mode'];
        $modelMap = \Utils\Constants::TOP_TEN_MODELS_MAP;

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
