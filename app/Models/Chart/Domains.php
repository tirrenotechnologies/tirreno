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

class Domains extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getData(int $apiKey): array {
        $field1 = 'unique_domains_count';
        $data1 = $this->getFirstLine($apiKey);

        $field2 = 'ts_new_domains';
        $data2 = $this->getSecondLine($apiKey);

        $data0 = $this->concatDataLines($data1, $field1, $data2, $field2);

        $indexedData = array_values($data0);
        $ox = array_column($indexedData, 'ts');
        $l1 = array_column($indexedData, $field1);
        $l2 = array_column($indexedData, $field2);

        return $this->addEmptyDays([$ox, $l1, $l2]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(DISTINCT event_email.domain) AS unique_domains_count
            FROM
                event

            INNER JOIN event_email
            ON (event.email = event_email.id)

            WHERE
                event.key = :api_key AND
                event.time >= :start_time AND
                event.time <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }

    private function getSecondLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_domain.created + :offset))::bigint AS ts,
                COUNT(event_domain.id) AS ts_new_domains
            FROM
                event_domain

            WHERE
                event_domain.key = :api_key AND
                event_domain.created >= :start_time AND
                event_domain.created <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey, false);
    }
}
