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

namespace Tirreno\Controllers\Admin\Blacklist;

class Data extends \Tirreno\Controllers\Admin\Base\Data {
    public function getList(int $apiKey): array {
        $model = new \Tirreno\Models\Grid\Blacklist\Grid($apiKey);

        return $model->getAll();
    }

    public function removeItemFromBlacklist(int $itemId, string $type, int $apiKey): void {
        $model = null;

        switch ($type) {
            case 'ip':
                $model = new \Tirreno\Models\Ip();
                break;
            case 'email':
                $model = new \Tirreno\Models\Email();
                break;
            case 'phone':
                $model = new \Tirreno\Models\Phone();
                break;
        }

        if ($model) {
            $model->updateFraudFlag([$itemId], false, $apiKey);
        }
    }

    public function setBlacklistUsersCount(bool $cache, int $apiKey): array {
        $operator = \Tirreno\Utils\Routes::getCurrentRequestOperator();

        if (!$operator) {
            $key = \Tirreno\Entities\ApiKey::getById($apiKey);
            $operator = \Tirreno\Entities\Operator::getById($key->creator);
        }

        $takeFromCache = $this->canTakeNumberOfBlacklistUsersFromCache($operator);

        $total = $operator->blacklistUsersCnt;
        if (!$cache || !$takeFromCache) {
            $total = (new \Tirreno\Models\Dashboard())->getTotalBlockedUsers(null, $apiKey);

            $model = new \Tirreno\Models\Operator();
            $model->updateBlacklistUsersCnt($total, $operator->id);
        }

        return ['total' => $total];
    }

    private function canTakeNumberOfBlacklistUsersFromCache(\Tirreno\Entities\Operator $operator): bool {
        $interval = \Base::instance()->get('REVIEWED_QUEUE_CNT_CACHE_TIME');

        return !!\Tirreno\Utils\DateRange::inIntervalTillNow($operator->reviewQueueUpdatedAt, $interval);
    }
}
