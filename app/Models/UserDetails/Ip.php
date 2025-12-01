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

namespace Models\UserDetails;

class Ip extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_ip';

    public function getDetails(int $userId, int $apiKey): array {
        $details = $this->getIpsDetails($userId, $apiKey);
        $data = [];

        if (count($details)) {
            $data = [
                'accountid' => $userId,

                'withdc'            => $details['data_center'],
                'withar'            => $details['relay'],
                // 'withsl'         => $details['starlink'],
                'withvpn'           => $details['vpn'],
                'withtor'           => $details['tor'],
                'sharedips'         => $details['shared'] > 1,
                'fraud_detected'    => $details['fraud_detected'],
                'spamlist'          => $details['blocklist'],
            ];
        }

        return $data;
    }

    private function getIpsDetails(int $userId, int $apiKey): array {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event.account AS accountid,
                BOOL_OR(event_ip.data_center) AS data_center,
                BOOL_OR(event_ip.relay) AS relay,
                BOOL_OR(event_ip.starlink) AS starlink,
                BOOL_OR(event_ip.vpn) AS vpn,
                BOOL_OR(event_ip.tor) AS tor,
                MAX(event_ip.shared) AS shared,
                BOOL_OR(event_ip.fraud_detected) AS fraud_detected,
                BOOL_OR(event_ip.blocklist) AS blocklist,
                BOOL_OR(event_ip.checked) AS checked

            FROM
                event_ip

            INNER JOIN event
            ON (event_ip.id = event.ip)

            WHERE
                event_ip.key = :api_key AND
                event.account = :user_id
            GROUP BY event.account'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }
}
