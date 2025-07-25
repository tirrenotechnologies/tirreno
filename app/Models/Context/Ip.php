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

namespace Models\Context;

class Ip extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $records = $this->getDetails($accountIds, $apiKey);
        $recordsByAccount = $this->groupRecordsByAccount($records);

        foreach ($recordsByAccount as $key => $value) {
            $recordsByAccount[$key] = [
                'eip_ip_id'             => array_column($value, 'eip_ip_id'),
                'eip_ip'                => array_column($value, 'eip_ip'),
                'eip_cidr'              => array_column($value, 'eip_cidr'),
                'eip_data_center'       => array_column($value, 'eip_data_center'),
                'eip_tor'               => array_column($value, 'eip_tor'),
                'eip_vpn'               => array_column($value, 'eip_vpn'),
                'eip_relay'             => array_column($value, 'eip_relay'),
                'eip_starlink'          => array_column($value, 'eip_starlink'),
                'eip_total_visit'       => array_column($value, 'eip_total_visit'),
                'eip_blocklist'         => array_column($value, 'eip_blocklist'),
                'eip_shared'            => array_column($value, 'eip_shared'),
                //'eip_domains'         => $this->getUniqueArray(array_column($value, 'eip_domains')),
                'eip_domains_count_len' => array_column($value, 'eip_domains_count_len'),
                'eip_country_id'        => array_column($value, 'eip_country_id'),
                'eip_fraud_detected'    => array_column($value, 'eip_fraud_detected'),
                'eip_alert_list'        => array_column($value, 'eip_alert_list'),
            ];
        }

        return $recordsByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT DISTINCT
                event.account                                   AS accountid,

                event_ip.id                                     AS eip_ip_id,
                event_ip.ip                                     AS eip_ip,
                event_ip.cidr::text                             AS eip_cidr,
                event_ip.country                                AS eip_country_id,
                event_ip.data_center                            AS eip_data_center,
                event_ip.tor                                    AS eip_tor,
                event_ip.vpn                                    AS eip_vpn,
                event_ip.relay                                  AS eip_relay,
                event_ip.starlink                               AS eip_starlink,
                event_ip.total_visit                            AS eip_total_visit,
                event_ip.blocklist                              AS eip_blocklist,
                event_ip.shared                                 AS eip_shared,
                -- event_ip.domains_count                          AS eip_domains,
                json_array_length(event_ip.domains_count::json) AS eip_domains_count_len,
                event_ip.fraud_detected                         AS eip_fraud_detected,
                event_ip.alert_list                             AS eip_alert_list

            FROM
                event_ip

            INNER JOIN event
            ON (event_ip.id = event.ip)

            WHERE
                event_ip.key = :api_key
                AND event_ip.checked = 'True'
                AND event.account IN ({$placeHolders})

            -- ORDER BY event_ip.id DESC"
        );

        if (count($accountIds) === 1) {
            $query .= ' LIMIT 100 OFFSET 0';
        }

        return $this->execQuery($query, $params);
    }
}
