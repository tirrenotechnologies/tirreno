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

class Totals extends Base {
    // execute before risk score!
    public function process(): void {
        $this->addLog('Start totals calculation.');

        $start = time();
        $models = tirreno('utils')->constants->REST_TOTALS_MODELS;

        $batchSize = tirreno('utils')->variables->getAccountOperationQueueBatchSize();
        $bottom = false;

        // TODO check multiple batches
        $keys = tirreno('models')->queue->getNextBatchKeys(tirreno('utils')->constants->RISK_SCORE_QUEUE_ACTION_TYPE, $batchSize);
        $res = [];

        $processed = [];
        $awaited = [];

        foreach (array_keys($models) as $name) {
            foreach ($keys as $key) {
                $awaited[] = strval($key) . ':' . $name;
            }
        }

        foreach ($models as $name => $modelClass) {
            $res[$name] = ['cnt' => 0, 's' => 0];
            $timeMark = time();
            $model = new $modelClass();
            foreach ($keys as $key) {
                tirreno('models')->sessionStat->updateStats($key);

                $cnt = $model->updateAllTotals($key);
                $res[$name]['cnt'] += $cnt;

                tirreno('log')->debug('Updated totals and stats for key %d.', $key);

                $processed[] = strval($key) . ':' . $name;

                if (time() - $start > tirreno('utils')->constants->ACCOUNT_OPERATION_QUEUE_EXECUTE_TIME_SEC) {
                    $missed = array_values(array_diff($processed, $awaited));
                    tirreno('log')->debug('Break totals update due to time limit. Processed model + key pairs -- %s. Missing -- %s.', json_encode($processed), json_encode($missed));

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
