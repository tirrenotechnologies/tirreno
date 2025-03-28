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

namespace Models\TopTen;

class ResourcesByUsers extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getList(int $apiKey, ?array $dateRange): array {
        $params = $this->getQueryParams($apiKey, $dateRange);

        $queryConditions = $this->getQueryConditions($dateRange);
        $queryConditions = join(' AND ', $queryConditions);

        $query = (
            "SELECT
                event_url.url,
                event_url.title,
                event_url.id                  AS url_id,
                COUNT(DISTINCT event.account) AS value

            FROM
                event

            INNER JOIN event_url
            ON (event.url = event_url.id)

            FULL OUTER JOIN event_url_query
            ON (event.query = event_url_query.id)

            WHERE
                {$queryConditions}

            GROUP BY
                event_url.id

            ORDER BY
                value DESC

            LIMIT 10 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
