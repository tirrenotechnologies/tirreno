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

namespace Models;

class Users extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_account';

    public function getAllUsersIdsOrdered(int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_account.id AS accountid,
                event_account.userid AS accounttitle,
                event_account.lastseen,
                event_email.email

            FROM
                event_account

            LEFT JOIN event_email
            ON event_account.lastemail = event_email.id

            WHERE
                event_account.key = :api_key

            ORDER BY event_account.id DESC'
        );

        return $this->execQuery($query, $params);
    }

    public function getTotalUsers(int $apiKey): int {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(event_account.id)

            FROM
                event_account

            WHERE
                event_account.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedUsers(int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT DISTINCT
                event.account AS id
            FROM
                event
            LEFT JOIN event_ip ON event.ip = event_ip.id
            WHERE
                event.key = :api_key AND
                event_ip.checked IS FALSE'
        );
        $result = array_column($this->execQuery($query, $params), 'id');

        // email + domain
        $query = (
            'SELECT DISTINCT
                event_email.account_id AS id
            FROM
                event_email
            LEFT JOIN event_domain ON event_email.domain = event_domain.id
            WHERE
                event_email.key = :api_key AND
                (event_email.checked IS FALSE OR event_domain.checked IS FALSE)'
        );
        $result = array_merge($result, array_column($this->execQuery($query, $params), 'id'));

        // phone
        $query = (
            'SELECT DISTINCT
                event_phone.account_id AS id
            FROM
                event_phone
            WHERE
                event_phone.key = :api_key AND
                event_phone.checked IS FALSE'
        );
        $result = array_merge($result, array_column($this->execQuery($query, $params), 'id'));

        // device
        $query = (
            'SELECT DISTINCT
                event_device.account_id AS id
            FROM
                event_device
            LEFT JOIN event_ua_parsed ON event_device.user_agent = event_ua_parsed.id
            WHERE
                event_device.key = :api_key AND
                event_ua_parsed.checked IS FALSE'
        );
        $result = array_merge($result, array_column($this->execQuery($query, $params), 'id'));

        return array_unique($result);
    }
}
