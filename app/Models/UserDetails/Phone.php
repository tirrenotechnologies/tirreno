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

class Phone extends \Models\BaseSql {
    use \Traits\Enrichment\Emails;

    protected $DB_TABLE_NAME = 'event_account';

    public function getDetails(int $userId, int $apiKey): array {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_phone.phone_number AS phonenumber,
                event_phone.type,
                event_phone.carrier_name,
                event_phone.invalid,
                event_phone.shared,
                event_phone.fraud_detected,

                countries.iso   AS country_iso,
                countries.value AS full_country

            FROM event_account

            LEFT JOIN event_phone
            ON (event_account.lastphone = event_phone.id)

            LEFT JOIN countries
            ON (event_phone.country_code = countries.id)

            WHERE
                event_account.id = :user_id AND
                event_account.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }
}
