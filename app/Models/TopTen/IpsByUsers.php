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

class IpsByUsers extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getList(int $apiKey, ?array $dateRange): array {
        $params = $this->getQueryParams($apiKey, $dateRange);

        $queryConditions = $this->getQueryConditions($dateRange);
        $queryConditions[] = 'event_ip.shared > 1';
        $queryConditions = join(' AND ', $queryConditions);

        $query = (
            "SELECT
                MAX(event_ip.ip) as ip,
                MAX(event_ip.id) as ipid,
                MAX(event_ip.shared) as value,
                MAX(countries.id) as country_id,
                MAX(event_isp.name) as isp_name

            FROM
                event

            INNER JOIN event_ip
            ON (event.ip = event_ip.id)

            INNER JOIN countries
            ON (event_ip.country = countries.id)

            INNER JOIN event_isp
            ON (event_ip.isp = event_isp.id)

            WHERE
                {$queryConditions}

            GROUP BY
                event_ip.ip

            ORDER BY
                value DESC

            LIMIT 10 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
