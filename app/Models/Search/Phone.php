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

namespace Models\Search;

class Phone extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_phone';

    public function searchByPhone(string $query, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':query' => "%{$query}%",
        ];

        $query = (
            "SELECT
                event_phone.account_id      AS id,
                'Phone'                     AS \"groupName\",
                'id'                        AS \"entityId\",
                event_phone.phone_number    AS value

            FROM
                event_phone

            WHERE
                LOWER(event_phone.phone_number) LIKE LOWER(:query) AND
                event_phone.key = :api_key

            LIMIT 25 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
