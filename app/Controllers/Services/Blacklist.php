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

class Blacklist extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        return tirreno('grids')->blacklist->getAll($apiKey);
    }

    public function getChart(int $apiKey): array {
        return tirreno('charts')->blacklist->getData($apiKey);
    }

    public function removeItemFromBlacklist(int $itemId, string $type, int $apiKey): void {
        if (!in_array($type, ['ip', 'email', 'phone'])) {
            return;
        }

        tirreno('models')->$type->updateFraudFlag([$itemId], false, $apiKey);
    }

    public function setBlacklistUsersCount(bool $cache, int $apiKey): array {
        $operator = tirreno('utils')->routes->getCurrentRequestOperator();

        if ($operator->isGuest()) {
            $key = tirreno('entities')->apiKey->getById($apiKey);
            $operator = tirreno('entities')->operator->getById($key->creator);
        }

        $takeFromCache = $this->canTakeNumberOfBlacklistUsersFromCache($operator);

        $total = $operator->blacklistUsersCnt;
        if (!$cache || !$takeFromCache) {
            $total = tirreno('models')->dashboard->getTotalBlockedUsers(null, $apiKey);

            tirreno('models')->operator->updateBlacklistUsersCnt($total, $operator->id);
        }

        return ['total' => $total];
    }

    private function canTakeNumberOfBlacklistUsersFromCache(\Tirreno\Entities\Operator $operator): bool {
        $interval = tirreno('storage')->get('REVIEWED_QUEUE_CNT_CACHE_TIME');

        return !!tirreno('utils')->dateRange->inIntervalTillNow($operator->reviewQueueUpdatedAt, $interval);
    }
}
