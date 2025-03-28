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

class UsersByEvents extends Base {
    protected $DB_TABLE_NAME = 'event';

    public function getList(int $apiKey, ?array $dateRange): array {
        $params = $this->getQueryParams($apiKey, $dateRange);

        $queryConditions = $this->getQueryConditions($dateRange);
        $queryConditions = join(' AND ', $queryConditions);

        $query = (
            "SELECT
                event_account.id            AS accountid,
                event_account.userid        AS accounttitle,
                event_account.fraud,
                event_account.score,
                event_account.score_updated_at,
                event_email.email,
                COUNT(event_account.userid) AS value

            FROM
                event

            INNER JOIN event_account
            ON (event.account = event_account.id)

            LEFT JOIN event_email
            ON (event_account.lastemail = event_email.id)

            WHERE
                {$queryConditions}

            GROUP BY
                event_account.id,
                event_account.userid,
                event_email.email

            ORDER BY
                value DESC

            LIMIT 10 OFFSET 0"
        );

        return $this->execQuery($query, $params);
    }
}
