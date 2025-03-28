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

namespace Crons;

class QueuesClearer extends AbstractCron {
    public function clearQueues(): void {
        $daysAgo = \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_CLEAR_COMPLETED_AFTER_DAYS');
        $clearBefore = new \DateTime(sprintf('%s days ago', $daysAgo));

        $actionTypes = [
            new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Blacklist),
            new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Delete),
            new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::CalulcateRiskScore),
        ];

        $clearedCount = 0;

        foreach ($actionTypes as $type) {
            $queue = new \Models\Queue\AccountOperationQueue($type);
            $clearedCount += $queue->clearCompleted($clearBefore);
        }

        $this->log(sprintf('Cleared %s completed items.', $clearedCount));
    }
}
