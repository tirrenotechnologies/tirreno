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

namespace Models\Chart;

class ReviewQueue extends Base {
    use \Traits\DateRange;

    protected $DB_TABLE_NAME = 'event';

    public function getData(int $apiKey): array {
        $data0 = [];
        $data1 = $this->getFirstLine($apiKey);
        $iters = count($data1);

        for ($i = 0; $i < $iters; ++$i) {
            $item = $data1[$i];
            $ts = $item['ts'];
            $fraud = $item['fraud'];

            if (!isset($data0[$ts])) {
                $data0[$ts] = [
                    'ts' => $ts,
                    'ts_new_users_whitelisted' => 0,
                    'ts_new_users_on_review' => 0,
                    'ts_new_users_blacklisted' => 0,
                ];
            }

            if ($fraud === false) {
                ++$data0[$ts]['ts_new_users_whitelisted'];
            } elseif ($fraud === true) {
                ++$data0[$ts]['ts_new_users_blacklisted'];
            } else {
                ++$data0[$ts]['ts_new_users_on_review'];
            }
        }

        $indexedData    = array_values($data0);
        $timestamps     = array_column($indexedData, 'ts');
        $line1          = array_column($indexedData, 'ts_new_users_whitelisted');
        $line2          = array_column($indexedData, 'ts_new_users_on_review');
        $line3          = array_column($indexedData, 'ts_new_users_blacklisted');

        return $this->addEmptyDays([$timestamps, $line1, $line2, $line3]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, COALESCE(event_account.latest_decision, event_account.lastseen) + :offset))::bigint AS ts,
                event_account.id,
                event_account.reviewed,
                event_account.fraud
            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.lastseen >= :start_time AND
                event_account.lastseen <= :end_time

            GROUP BY ts, event_account.id
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey, false);
    }
}
