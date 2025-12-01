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

namespace Crons;

class BatchedNewEvents extends Base {
    protected function readyToProcess(): bool {
        $model = new \Models\Cursor();

        // was not locked; locking now
        if ($model->safeLock()) {
            return true;
        }

        $result = $model->getLock();

        if (\Utils\DateRange::isQueueTimeouted($result['updated'])) {
            return false;
        }

        $model->forceLock();

        return true; // relocked
    }

    public function process(): void {
        if (!$this->readyToProcess()) {
            $this->addLog('Could not acquire the lock; another cron is probably already working on recently added events.');

            return;
        }

        $model = new \Models\Cursor();

        try {
            $cursor = $model->getCursor();
            $next = $model->getNextCursor($cursor, \Utils\Variables::getNewEventsBatchSize());

            if (!$next) {
                $this->addLog('No new events.');
                $model->unlock();

                return;
            }

            $accounts = (new \Models\Events())->getDistinctAccounts($cursor, $next);

            \Utils\Routes::callExtra('BATCHING_NEW_EVENTS', $cursor, $next);

            (new \Models\Queue())->addBatch($accounts, \Utils\Constants::get('RISK_SCORE_QUEUE_ACTION_TYPE'));

            $model->updateCursor($next);

            // TODO: Log new events cursor to database?
            $this->addLog('Updated \'last_event_id\' in \'queue_new_events_cursor\' table to ' . strval($next));
            $this->addLog(sprintf('Added %s accounts to the risk score queue.', count($accounts)));
        } catch (\Throwable $e) {
            $this->addLog(sprintf('Batched new events error %s.', $e->getMessage()));
        }

        $model->unlock();
    }
}
