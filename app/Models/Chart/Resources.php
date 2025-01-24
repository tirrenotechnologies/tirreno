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

class Resources extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $ox = array_column($data, 'ts');
        $l1 = array_column($data, 'count_200');
        $l2 = array_column($data, 'count_404');
        $l3 = array_column($data, 'count_500');

        return $this->addEmptyDays([$ox, $l1, $l2, $l3]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(DISTINCT event.id) AS url_count,

                COUNT(DISTINCT (
                    CASE WHEN event.http_code=200 OR event.http_code IS NULL THEN event.id END)
                ) AS count_200,

                COUNT(DISTINCT (
                    CASE WHEN event.http_code = 404 THEN event.id END)
                ) AS count_404,

                COUNT(DISTINCT (
                    CASE WHEN event.http_code IN(403, 500) THEN event.id END)
                ) AS count_500

            FROM
                event

            WHERE
                event.key = :api_key AND
                event.time >= :start_time AND
                event.time <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }
}
