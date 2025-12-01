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

namespace Controllers\Admin\ReviewQueue;

class Data extends \Controllers\Admin\Base\Data {
    public function getList(int $apiKey): array {
        $model = new \Models\Grid\ReviewQueue\Grid($apiKey);

        return $model->getAllUnderReviewUsers() ?? [];
    }

    public function setNotReviewedCount(bool $cache, int $apiKey): array {
        $currentOperator = \Utils\Routes::getCurrentRequestOperator();

        if (!$currentOperator) {
            $model = new \Models\ApiKeys();
            $model = $model->getKeyById($apiKey);
            $creator = $model->creator;
            $model = new \Models\Operator();
            $currentOperator = $model->getOperatorById($creator);
        }

        $takeFromCache = $this->canTakeNumberOfNotReviewedUsersFromCache($currentOperator);

        $total = $currentOperator->review_queue_cnt;
        if (!$cache || !$takeFromCache) {
            $total = (new \Models\ReviewQueue())->getCountShort($apiKey);

            $data = [
                'id' => $currentOperator->id,
                'review_queue_cnt' => $total,
            ];

            $model = new \Models\Operator();
            $model->updateReviewedQueueCnt($data);
        }

        return ['total' => $total];
    }

    private function canTakeNumberOfNotReviewedUsersFromCache(\Models\Operator $currentOperator): bool {
        $interval = \Base::instance()->get('REVIEWED_QUEUE_CNT_CACHE_TIME');

        return !!\Utils\DateRange::inIntervalTillNow($currentOperator->review_queue_updated_at, $interval);
    }
}
