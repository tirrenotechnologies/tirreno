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
        $field1 = 'ts_new_users_whitelisted';
        $data1  = $this->getFirstLine($apiKey);

        $field2 = 'ts_new_added_to_review';
        $data2  = $this->getSecondLine($apiKey);

        $field3 = 'ts_new_users_blacklisted';
        $data3  = $this->getThirdLine($apiKey);

        $data0 = $this->concatDataLines($data1, $field1, $data2, $field2, $data3, $field3);

        $indexedData    = array_values($data0);
        $timestamps     = array_column($indexedData, 'ts');
        $line1          = array_column($indexedData, $field1);
        $line2          = array_column($indexedData, $field2);
        $line3          = array_column($indexedData, $field3);

        return $this->addEmptyDays([$timestamps, $line1, $line2, $line3]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.latest_decision + :offset))::bigint AS ts,
                COUNT(event_account.id) as ts_new_users_whitelisted
            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.fraud IS FALSE AND
                event_account.latest_decision >= :start_time AND
                event_account.latest_decision <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey, false);
    }

    private function getSecondLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.added_to_review + :offset))::bigint AS ts,
                COUNT(event_account.id) AS ts_new_added_to_review
            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.added_to_review IS NOT NULL AND
                event_account.added_to_review >= :start_time AND
                event_account.added_to_review <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey, false);
    }

    private function getThirdLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_account.latest_decision + :offset))::bigint AS ts,
                COUNT(event_account.id) as ts_new_users_blacklisted
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

        return $this->execute($query, $apiKey, false);
    }
}
