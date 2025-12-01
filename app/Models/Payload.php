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

namespace Models;

class Payload extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_payload';

    public function getByEventId(int $eventId, int $apiKey): ?string {
        $params = [
            ':event_id' => $eventId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                event_payload.payload
            FROM
                event
            LEFT JOIN event_payload
            ON (event.payload = event_payload.id)
            WHERE
                event.id = :event_id AND
                event_payload.key = :api_key'
        );

        $result = $this->execQuery($query, $params);

        return count($result) ? $result[0]['payload'] : null;
    }
}
