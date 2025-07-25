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

class Bot extends BaseEventsCount {
    public function getCounts(int $apiKey): array {
        $query = (
            "SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event.time + :offset))::bigint AS ts,
                COUNT(CASE WHEN event_type.value IN ({$this->normalFlatIds})  THEN TRUE END) AS event_normal_type_count,
                COUNT(CASE WHEN event_type.value IN ({$this->editFlatIds})    THEN TRUE END) AS event_editing_type_count,
                COUNT(CASE WHEN event_type.value IN ({$this->alertFlatIds})   THEN TRUE END) AS event_alert_type_count

            FROM
                event

            LEFT JOIN event_type
            ON event.type = event_type.id

            INNER JOIN event_device
            ON (event.device = event_device.id)

            INNER JOIN event_ua_parsed
            ON (event_device.user_agent = event_ua_parsed.id)

            WHERE
                event_ua_parsed.id = :id AND
                event.key = :api_key AND
                event.time >= :start_time AND
                event.time <= :end_time

            GROUP BY ts
            ORDER BY ts"
        );

        return $this->executeOnRangeById($query, $apiKey);
    }
}
