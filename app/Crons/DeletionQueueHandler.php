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

class DeletionQueueHandler extends AbstractQueueCron {
    public function __construct() {
        parent::__construct();

        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Delete);
        $this->accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);
    }

    public function processQueue(): void {
        if ($this->accountOpQueueModel->isExecuting() && !$this->accountOpQueueModel->unclog()) {
            $this->log('Deletion queue is already being executed by another cron job.');
        } else {
            $this->processItems($this->accountOpQueueModel);
        }
    }

    protected function processItem(array $item): void {
        $user = new \Models\User();
        $user->deleteAllUserData($item['event_account'], $item['key']);
    }
}
