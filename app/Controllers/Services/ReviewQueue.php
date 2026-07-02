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

class ReviewQueue extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        return tirreno('grids')->reviewQueue->getAll($apiKey);
    }

    public function getChart(int $apiKey): array {
        return tirreno('charts')->reviewQueue->getData($apiKey);
    }

    public function setNotReviewedCount(bool $cache, int $apiKey): array {
        $operator = tirreno('utils')->routes->getCurrentRequestOperator();

        if ($operator->isGuest()) {
            $key = tirreno('entities')->apiKey->getById($apiKey);
            $operator = tirreno('entities')->operator->getById($key->creator);
        }

        $takeFromCache = $this->canTakeNumberOfNotReviewedUsersFromCache($operator);

        $total = $operator->reviewQueueCnt;
        if (!$cache || !$takeFromCache) {
            $total = tirreno('models')->reviewQueue->getCount($apiKey);

            tirreno('models')->operator->updateReviewedQueueCnt($total, $operator->id);
        }

        return ['total' => $total];
    }

    private function canTakeNumberOfNotReviewedUsersFromCache(\Tirreno\Entities\Operator $operator): bool {
        $interval = tirreno('storage')->get('REVIEWED_QUEUE_CNT_CACHE_TIME');

        return !!tirreno('utils')->dateRange->inIntervalTillNow($operator->reviewQueueUpdatedAt, $interval);
    }

    public function addToBlacklist(int $userId, int $apiKey): false|int {
        if (!tirreno('controllers')->user->checkIfOperatorHasAccess($userId, $apiKey)) {
            return false;
        }

        tirreno('controllers')->user->addToBlacklistQueue($userId, true, false, true, $apiKey);   // recalculate

        return tirreno('utils')->errorCodes->USER_FRAUD_FLAG_SET;
    }

    public function addToWhitelist(int $userId, int $apiKey): false|int {
        if (!tirreno('controllers')->user->checkIfOperatorHasAccess($userId, $apiKey)) {
            return false;
        }

        tirreno('controllers')->user->addToBlacklistQueue($userId, false, false, true, $apiKey);   // recalculate

        return tirreno('utils')->errorCodes->USER_FRAUD_FLAG_UNSET;
    }
}
