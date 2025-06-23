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

class Users extends Base {
    use \Traits\DateRange;

    protected $DB_TABLE_NAME = 'event_account';

    public function getData(int $apiKey): array {
        $data0 = [];
        $data1 = $this->getFirstLine($apiKey);
        $iters = count($data1);

        for ($i = 0; $i < $iters; ++$i) {
            $item = $data1[$i];
            $ts = $item['ts'];
            $score = $item['score'];

            if (!isset($data0[$ts])) {
                $data0[$ts] = [
                    'ts' => $ts,
                    'ts_new_users_with_trust_score_high' => 0,
                    'ts_new_users_with_trust_score_medium' => 0,
                    'ts_new_users_with_trust_score_low' => 0,
                ];
            }

            $inf = \Utils\Constants::get('USER_HIGH_SCORE_INF');
            if ($score >= \Utils\Constants::get('USER_HIGH_SCORE_INF')) {
                ++$data0[$ts]['ts_new_users_with_trust_score_high'];
            }

            $inf = \Utils\Constants::get('USER_MEDIUM_SCORE_INF');
            $sup = \Utils\Constants::get('USER_MEDIUM_SCORE_SUP');
            if ($score >= $inf && $score < $sup) {
                ++$data0[$ts]['ts_new_users_with_trust_score_medium'];
            }

            $inf = \Utils\Constants::get('USER_LOW_SCORE_INF');
            $sup = \Utils\Constants::get('USER_LOW_SCORE_SUP');
            if ($score >= $inf && $score < $sup) {
                ++$data0[$ts]['ts_new_users_with_trust_score_low'];
            }
        }

        $indexedData    = array_values($data0);
        $timestamps     = array_column($indexedData, 'ts');
        $line1          = array_column($indexedData, 'ts_new_users_with_trust_score_high');
        $line2          = array_column($indexedData, 'ts_new_users_with_trust_score_medium');
        $line3          = array_column($indexedData, 'ts_new_users_with_trust_score_low');

        return $this->addEmptyDays([$timestamps, $line1, $line2, $line3]);
    }

    private function getFirstLine(int $apiKey) {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.created + :offset))::bigint AS ts,
                event_account.id,
                event_account.score
            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.created >= :start_time AND
                event_account.created <= :end_time

            GROUP BY ts, event_account.id
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }
}
