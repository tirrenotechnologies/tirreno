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

namespace Models\Context;

class Ip extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $details = $this->getIpDetails($accountIds, $apiKey);

        $recordsByAccount = [];

        foreach ($details as $record) {
            $accountId = $record['accountid'];
            if (!isset($recordsByAccount[$accountId])) {
                $recordsByAccount[$accountId] = [
                    'eip_ip_id'         => [],
                    'eip_cidr_count'    => [],
                    'eip_country_count' => [],
                ];
            }

            $ipId       = $record['ip'];
            $cidr       = $record['cidr'];
            $countryId  = $record['country'];
            if (!isset($recordsByAccount[$accountId]['eip_ip_id'][$ipId])) {
                $recordsByAccount[$accountId]['eip_ip_id'][$ipId] = [
                    'cidr'      => $cidr,
                    'country'   => $countryId,
                ];
            }

            if (!isset($recordsByAccount[$accountId]['eip_cidr_count'][$cidr])) {
                $recordsByAccount[$accountId]['eip_cidr_count'][$cidr] = 0;
            }

            if (!isset($recordsByAccount[$accountId]['eip_country_count'][$countryId])) {
                $recordsByAccount[$accountId]['eip_country_count'][$countryId] = 0;
            }

            $recordsByAccount[$accountId]['eip_cidr_count'][$cidr]++;
            $recordsByAccount[$accountId]['eip_country_count'][$countryId]++;
        }

        $records = $this->getDetails($accountIds, $apiKey);

        foreach ($records as $record) {
            $accountId = $record['accountid'];
            $recordsByAccount[$accountId]['eip_data_center']        = $record['eip_data_center'];               // bool
            $recordsByAccount[$accountId]['eip_tor']                = $record['eip_tor'];                       // bool
            $recordsByAccount[$accountId]['eip_vpn']                = $record['eip_vpn'];                       // bool
            $recordsByAccount[$accountId]['eip_starlink']           = $record['eip_starlink'];                  // bool
            $recordsByAccount[$accountId]['eip_blocklist']          = $record['eip_blocklist'];                 // bool
            $recordsByAccount[$accountId]['eip_has_fraud']          = $record['eip_has_fraud'];                 // bool
            $recordsByAccount[$accountId]['eip_lan']                = $record['eip_lan'];                       // bool
            $recordsByAccount[$accountId]['eip_shared']             = $record['eip_shared'];                    // int
            $recordsByAccount[$accountId]['eip_domains_count_len']  = $record['eip_domains_count_len'];         // int
            $recordsByAccount[$accountId]['eip_unique_cidrs']       = $record['eip_unique_cidrs'];              // int
            $recordsByAccount[$accountId]['eip_country_id']         = json_decode($record['eip_country_id']);   // array
        }

        return $recordsByAccount;
    }

    protected function getIpDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        // count account related ips in cidr and country in php
        $query = (
            "SELECT
                event.account                       AS accountid,
                event_ip.id                         AS ip,
                event_ip.cidr::text                 AS cidr,
                event_ip.country                    AS country

            FROM
                event_ip

            INNER JOIN event
            ON (event_ip.id = event.ip)

            WHERE
                event.account IN ({$placeHolders}) AND
                event_ip.checked IS TRUE AND
                event_ip.key = :api_key

            GROUP BY event.account, event_ip.id
            ORDER BY event_ip.lastseen DESC"
        );

        if (count($accountIds) === 1) {
            $query .= ' LIMIT 100 OFFSET 0';
        }

        return $this->execQuery($query, $params);
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event.account                                                                       AS accountid,

                COALESCE(BOOL_OR(event_ip.data_center), false)                                      AS eip_data_center,
                COALESCE(BOOL_OR(event_ip.tor), false)                                              AS eip_tor,
                COALESCE(BOOL_OR(event_ip.vpn), false)                                              AS eip_vpn,
                COALESCE(BOOL_OR(event_ip.starlink), false)                                         AS eip_starlink,
                COALESCE(BOOL_OR(event_ip.blocklist), false)                                        AS eip_blocklist,
                COALESCE(BOOL_OR(event_ip.fraud_detected), false)                                   AS eip_has_fraud,
                COALESCE(MAX(event_ip.shared), 0)                                                   AS eip_shared,
                COALESCE(MAX(json_array_length(event_ip.domains_count::json)), 0)                   AS eip_domains_count_len,
                COALESCE(BOOL_OR(event_ip.cidr IS NULL AND event_ip.data_center IS FALSE), false)   AS eip_lan,
                array_to_json(array_agg(DISTINCT event_ip.country))                                 AS eip_country_id,
                COUNT(DISTINCT (event_ip.cidr IS NULL, event_ip.cidr))                              AS eip_unique_cidrs

            FROM
                event_ip

            INNER JOIN event
            ON (event_ip.id = event.ip)

            WHERE
                event.account IN ({$placeHolders}) AND
                event_ip.checked IS TRUE AND
                event_ip.key = :api_key

            GROUP BY event.account"
        );

        return $this->execQuery($query, $params);
    }
}
