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

class Totals extends Base {
    // execute before risk score!
    public function process(): void {
        $this->addLog('Start totals calculation.');

        $start = time();
        $models = \Utils\Constants::get('REST_TOTALS_MODELS');

        $batchSize = \Utils\Variables::getAccountOperationQueueBatchSize();
        $bottom = false;

        $queueModel = new \Models\Queue();

        // TODO check multiple batches
        $keys = $queueModel->getNextBatchKeys(\Utils\Constants::get('RISK_SCORE_QUEUE_ACTION_TYPE'), $batchSize);
        $res = [];

        foreach ($models as $name => $modelClass) {
            $res[$name] = ['cnt' => 0, 's' => 0];
            $timeMark = time();
            $model = new $modelClass();
            foreach ($keys as $key) {
                (new \Models\SessionStat())->updateStats($key);

                $cnt = $model->updateAllTotals($key);
                $res[$name]['cnt'] += $cnt;
                if (time() - $start > \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC')) {
                    // TODO: any reason to put the rest keys to queue?
                    $res[$name]['s'] = time() - $timeMark;
                    break 2;
                }
            }
            $res[$name]['s'] = time() - $timeMark;
        }


        $this->addLog(sprintf('Updated %s entities for %s keys and %s models in %s seconds.', array_sum(array_column(array_values($res), 'cnt')), count($keys), count($models), time() - $start));
    }
}
