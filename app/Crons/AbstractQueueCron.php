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

abstract class AbstractQueueCron extends AbstractCron {
    protected \Models\Queue\AccountOperationQueue $accountOpQueueModel;

    protected function processItems(): void {
        $this->log('Start processing queue.');

        $start = time();
        $success = $failed = [];

        $errors = [];

        do {
            $this->log(sprintf('Fetching next batch (%s) in queue.', \Utils\Variables::getAccountOperationQueueBatchSize()));
            $items = $this->accountOpQueueModel->getNextBatchInQueue(\Utils\Variables::getAccountOperationQueueBatchSize());

            if (!count($items)) {
                break;
            }

            $items = \array_reverse($items); // to use array_pop later.

            $this->accountOpQueueModel->setExecutingForBatch(\array_column($items, 'id'));

            while (time() - $start < \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC')) {
                $item = \array_pop($items); // array_pop has O(1) complexity, array_shift has O(n) complexity.
                if (!$item) {
                    break;
                }

                try {
                    $this->processItem($item);
                    $success[] = $item;
                } catch (\Throwable $e) {
                    $failed[] = $item;
                    $this->log(sprintf('Queue error %s.', $e->getMessage()));
                    $errors[] = sprintf('Error on %s: %s. Trace: %s', json_encode($item), $e->getMessage(), $e->getTraceAsString());
                }
            }
        } while (time() - $start < \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC')); // allow another batch to be fetched if time permits.

        $this->accountOpQueueModel->setCompletedForBatch(\array_column($success, 'id'));
        $this->accountOpQueueModel->setFailedForBatch(\array_column($failed, 'id'));
        $this->accountOpQueueModel->setWaitingForBatch(\array_column($items, 'id')); // unfinished items back to waiting.

        if (count($errors)) {
            $errObj = [
                'code'      => 500,
                'message'   => sprintf('Cron %s err', get_class($this)),
                //'trace'     => implode('; ', $errors),
                'trace'     => $errors[0],
                'sql_log'   => '',
            ];
            \Utils\ErrorHandler::saveErrorInformation($this->f3, $errObj);
        }

        $this->log(sprintf(
            'Processed %s items in %s seconds. %s items failed. %s items put back in queue.',
            count($success),
            time() - $start,
            count($failed),
            count($items),
        ));
    }

    abstract protected function processItem(array $item): void;
}
