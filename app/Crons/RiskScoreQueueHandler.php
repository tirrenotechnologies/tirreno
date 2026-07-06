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
    private object $rulesController;

    public function __construct() {
        $this->rulesController = tirreno('controllers')->rules;
        $this->rulesController->buildEvaluationModels();
    }

    public function process(): void {
        $batchSize = tirreno('utils')->variables->getAccountOperationQueueBatchSize();
        $keys = tirreno('models')->queue->getNextBatchKeys(tirreno('utils')->constants->RISK_SCORE_QUEUE_ACTION_TYPE, $batchSize);

        parent::baseProcess(tirreno('utils')->constants->RISK_SCORE_QUEUE_ACTION_TYPE);

        foreach ($keys as $key) {
            tirreno('controllers')->blacklist->setBlacklistUsersCount(false, $key);
            tirreno('controllers')->reviewQueue->setNotReviewedCount(false, $key);
        }
    }

    protected function processItem(array $item): void {
        $this->rulesController->evaluateUser($item['event_account'], $item['key'], true);
    }
}
