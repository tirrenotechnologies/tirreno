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

class Blacklist extends Base {
    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $ox = array_column($data, 'ts');
        $l1 = array_column($data, 'ts_new_records');

        return $this->addEmptyDays([$ox, $l1]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.latest_decision + :offset))::bigint AS ts,
                COUNT(*) AS ts_new_records
            FROM
                event_account
            WHERE
                event_account.key = :api_key AND
                event_account.fraud IS TRUE AND
                event_account.latest_decision >= :start_time AND
                event_account.latest_decision <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }
}
