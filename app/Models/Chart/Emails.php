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

class Emails extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $ox = array_column($data, 'ts');
        $l1 = array_column($data, 'email_count');
        $l2 = array_column($data, 'blockemails_count');

        return $this->addEmptyDays([$ox, $l1, $l2]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(DISTINCT event.email) AS email_count,
                COUNT(DISTINCT (
                    CASE WHEN event_email.blockemails IS TRUE THEN event.email END)
                ) AS blockemails_count
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
}
