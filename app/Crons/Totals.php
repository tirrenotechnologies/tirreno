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

class Totals extends AbstractCron {
    // execute before risk score!
    public function calculateTotals(): void {
        $this->log('Start totals calculation.');
        $start = time();
        $models = \Utils\Constants::get('REST_TOTALS_MODELS');

        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::CalulcateRiskScore);
        $queueModel = new \Models\Queue\AccountOperationQueue($actionType);
        $keys = $queueModel->getNextBatchKeysInQueue(\Utils\Variables::getAccountOperationQueueBatchSize());

        $res = [];

        foreach ($models as $name => $modelClass) {
            $res[$name] = ['cnt' => 0, 's' => 0];
            $s = time();
            $model = new $modelClass();
            foreach ($keys as $key) {
                $cnt = $model->updateAllTotals($key);
                $res[$name]['cnt'] += $cnt;
                if (time() - $start > \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC')) {
                    // TODO: any reason to put the rest keys to queue?
                    $res[$name]['s'] = time() - $s;
                    break 2;
                }
            }
            $res[$name]['s'] = time() - $s;
        }

        $this->log(sprintf('Updated %s entities for %s keys and %s models in %s seconds.', array_sum(array_column(array_values($res), 'cnt')), count($keys), count($models), time() - $start));
    }
}
