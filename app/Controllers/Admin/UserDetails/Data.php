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

namespace Tirreno\Controllers\Admin\UserDetails;

class Data extends \Tirreno\Controllers\Admin\Base\Data {
    public function getUserDetails(int $userId, int $apiKey): array {
        (new \Tirreno\Models\User())->updateTotalsByAccountIds([$userId], $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Id();
        $userDetails    = $model->getDetails($userId, $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Ip();
        $ipDetails      = $model->getDetails($userId, $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Total();
        $totalDetails   = $model->getDetails($userId, $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Behaviour();
        $offset         = \Tirreno\Utils\Timezones::getCurrentOperatorOffset();

        $dateRange      = \Tirreno\Utils\Timezones::getCurDayRange($offset);
        $dayDetails     = $model->getDayDetails($userId, $dateRange, $apiKey);

        $dateRange      = \Tirreno\Utils\Timezones::getWeekAgoDayRange($offset);
        $weekDetails    = $model->getDayDetails($userId, $dateRange, $apiKey);

        return [
            'userDetails'   => $userDetails,
            'ipDetails'     => $ipDetails,
            'totalDetails'  => $totalDetails,
            'dayDetails'    => $dayDetails,
            'weekDetails'   => $weekDetails,
        ];
    }

    public function checkIfOperatorHasAccess(int $userId, int $apiKey): bool {
        $model = new \Tirreno\Models\UserDetails\Id();

        return $model->checkAccess($userId, $apiKey);
    }
}
