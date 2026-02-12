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

namespace Tirreno\Crons;

class RiskScoreQueueHandler extends BaseQueue {
    private \Tirreno\Controllers\Admin\Rules\Data $rulesController;

    public function __construct() {
        $this->rulesController = new \Tirreno\Controllers\Admin\Rules\Data();
        $this->rulesController->buildEvaluationModels();
    }

    public function process(): void {
        $batchSize = \Tirreno\Utils\Variables::getAccountOperationQueueBatchSize();
        $queueModel = new \Tirreno\Models\Queue();
        $keys = $queueModel->getNextBatchKeys(\Tirreno\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE, $batchSize);

        parent::baseProcess(\Tirreno\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE);

        $blacklist = new \Tirreno\Controllers\Admin\Blacklist\Data();
        $reviewQueue = new \Tirreno\Controllers\Admin\ReviewQueue\Data();

        foreach ($keys as $key) {
            $blacklist->setBlacklistUsersCount(false, $key);
            $reviewQueue->setNotReviewedCount(false, $key);
        }
    }

    protected function processItem(array $item): void {
        $this->rulesController->evaluateUser($item['event_account'], $item['key'], true);
    }
}
