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

namespace Models\UserDetails;

class Id extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_account';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':user_id' => $subjectId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                userid

            FROM
                event_account

            WHERE
                event_account.id = :user_id
                AND event_account.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getDetails(int $userId, int $apiKey): array {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_account.userid,
                event_account.lastseen,
                event_account.created,
                event_account.firstname,
                event_account.lastname,
                event_account.score,
                event_account.score_details,
                event_account.is_important,
                event_account.fraud,
                event_account.reviewed,
                event_account.latest_decision,
                event_account.added_to_review,

                event_email.email

            FROM
                event_account

            LEFT JOIN event_email
            ON (event_account.lastemail = event_email.id)

            WHERE
                event_account.id = :user_id
                AND event_account.key = :api_key'
        );


        $results = $this->execQuery($query, $params);

        $result = $results[0] ?? [];

        $tsColumns = ['created', 'lastseen', 'score_updated_at', 'latest_decision', 'updated', 'added_to_review'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }
}
