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

namespace Controllers\Admin\UserDetails;

class Data extends \Controllers\Base {
    public function getUserDetails(int $userId, int $apiKey): array {
        $model          = new \Models\UserDetails\Id();
        $userDetails    = $model->getDetails($userId, $apiKey);

        $model          = new \Models\UserDetails\Ip();
        $ipDetails      = $model->getDetails($userId, $apiKey);

        $model          = new \Models\UserDetails\Behaviour();
        $offset         = \Utils\TimeZones::getCurrentOperatorOffset();

        $dateRange      = \Utils\TimeZones::getCurDayRange($offset);
        $dayDetails     = $model->getDayDetails($userId, $dateRange, $apiKey);

        $dateRange      = \Utils\TimeZones::getLastNDaysRange(7, $offset);
        $weekDetails    = $model->getWeekDetails($userId, $dateRange, $apiKey);

        return [
            'userDetails'   => $userDetails,
            'ipDetails'     => $ipDetails,
            'dayDetails'    => $dayDetails,
            'weekDetails'   => $weekDetails,
        ];
    }

    public function checkIfOperatorHasAccess(int $userId, int $apiKey): bool {
        $model = new \Models\UserDetails\Id();

        return $model->checkAccess($userId, $apiKey);
    }
}
