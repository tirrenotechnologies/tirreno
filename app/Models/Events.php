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

class Events extends \Models\BaseSql {
    use \Traits\DateRange;
    use \Traits\Enrichment\Ips;

    protected $DB_TABLE_NAME = 'event';

    public function getDistinctAccounts(int $after, int $to): array {
        $params = [
            ':cursor' => $after,
            ':next_cursor' => $to,
        ];

        $query = (
            'SELECT DISTINCT
                event.account AS "accountId",
                event.key
            FROM
                event
            JOIN
                event_account ON event.account = event_account.id
            WHERE
                event.id > :cursor
                AND event.id <= :next_cursor'
        );

        return $this->execQuery($query, $params);
    }

    private function getEvents(array $queryParams): array {
        $request = $this->f3->get('REQUEST');
        $dateRange = $this->getDatesRange($request);
        $data = $this->getData($queryParams, $dateRange);
        $total = $this->getTotal($queryParams, $dateRange);

        return [
            'draw' => $request['draw'] ?? 1,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data,
        ];
    }

    public function getEventsByUser(int $userId, int $apiKey): array {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        return $this->getEvents($params);
    }

    private function getData(array $params, ?array $dateRange): array {
        $query = (
            'SELECT
                event.id,
                event.time,
                event.http_code,

                event_ip.ip,
                event_ip.data_center,
                -- event_ip.asn,
                event_ip.vpn,
                event_ip.tor,
                event_ip.relay,
                event_ip.starlink,
                event_ip.id AS ipId,
                -- event_ip.description,
                -- event_ip.name as netname,
                event_ip.blocklist,
                event_ip.fraud_detected,
                event_ip.checked,

                event_isp.asn,
                event_isp.description,
                event_isp.name AS netname,

                event_url.url,
                event_url.id as url_id,
                event_url_query.query,
                event_url.title,
                event_referer.referer,
                -- event_account.is_important,
                event_account.id AS accountid,
                event_account.userid AS accounttitle,
                event_account.score,

                countries.id            AS country_id,
                countries.iso           AS country_iso,
                countries.value         AS full_country,
                event_device.id         AS deviceId,
                event_ua_parsed.device,
                event_ua_parsed.ua,
                event_ua_parsed.browser_name,
                event_ua_parsed.browser_version,
                event_ua_parsed.os_name,
                event_ua_parsed.os_version,
                event_ua_parsed.ua,
                event_type.value AS event_type,
                event_type.name AS event_type_name
            FROM
                event
            INNER JOIN event_account
            ON (event.account = event_account.id)

            INNER JOIN event_url
            ON (event.url = event_url.id)
            LEFT JOIN event_referer
            ON (event.referer = event_referer.id)
            -- FULL OUTER JOIN event_url_query
            LEFT JOIN event_url_query
            ON (event.query = event_url_query.id)
            INNER JOIN event_device
            ON (event.device = event_device.id)
            INNER JOIN event_type
            ON (event.type = event_type.id)
            INNER JOIN event_ua_parsed
            ON (event_device.user_agent = event_ua_parsed.id)
            INNER JOIN event_ip
            ON (event.ip = event_ip.id)
            LEFT JOIN event_isp
            ON (event_ip.isp = event_isp.id)
            INNER JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event.account   = :user_id AND
                event.key       = :api_key
                %s

            ORDER BY
                event.time DESC'
        );

        $this->applyDateRange($query, $params, $dateRange);
        $this->applyLimit($query, $params);

        $results = $this->execQuery($query, $params);
        $this->calculateIpType($results);

        return $results;
    }

    private function getTotal(array $params, ?array $dateRange): int {
        $query = (
            'SELECT
                COUNT(event.id)

            FROM
                event
            INNER JOIN event_account
            ON (event.account = event_account.id)

            INNER JOIN event_url
            ON (event.url = event_url.id)
            FULL OUTER JOIN event_url_query
            ON (event.query = event_url_query.id)
            INNER JOIN event_device
            ON (event.device = event_device.id)
            INNER JOIN event_ua_parsed
            ON (event_device.user_agent = event_ua_parsed.id)
            INNER JOIN event_ip
            ON (event.ip = event_ip.id)
            INNER JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event.account   = :user_id AND
                event.key       = :api_key
                %s'
        );

        $this->applyDateRange($query, $params, $dateRange);

        $results = $this->execQuery($query, $params);

        return $results[0]['count'];
    }

    private function applyDateRange(string &$query, array &$params, ?array $dateRange = null): void {
        $searchConditions = '';
        if ($dateRange) {
            $searchConditions = (
                'AND event.time >= :start_time
                AND event.time <= :end_time'
            );

            $params[':end_time'] = $dateRange['endDate'];
            $params[':start_time'] = $dateRange['startDate'];
        }

        $query = sprintf($query, $searchConditions);
    }

    protected function applyLimit(string &$query, array &$queryParams): void {
        $request = $this->f3->get('REQUEST');

        $start = $request['start'] ?? null;
        $length = $request['length'] ?? null;

        if (isset($start) && isset($length)) {
            $query .= ' LIMIT :length OFFSET :start';

            $queryParams[':start'] = $start;
            $queryParams[':length'] = $length;
        }
    }

    public function uniqueEntitesByUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];
        $query = (
            'SELECT DISTINCT
                event.ip,
                event_ip.isp,
                event_ip.country,
                event.url,
                event_email.domain,
                event_phone.phone_number
            FROM
                event
            LEFT JOIN event_ip
            ON event.ip = event_ip.id
            LEFT JOIN event_email
            ON event.email = event_email.id
            LEFT JOIN event_phone
            ON event.phone = event_phone.id
            LEFT JOIN event_country
            ON event_ip.country = event_country.country AND event_ip.key = event_country.key

            WHERE
                event.key = :api_key AND
                event.account = :user_id'
        );

        $results = $this->execQuery($query, $params);

        return [
            'ip_ids' => array_unique(array_column($results, 'ip')),
            'isp_ids' => array_unique(array_column($results, 'isp')),
            'country_ids' => array_unique(array_column($results, 'country')),
            'url_ids' => array_unique(array_column($results, 'url')),
            'domain_ids' => array_unique(array_column($results, 'domain')),
            'phone_numbers' => array_unique(array_column($results, 'phone_number')),
        ];
    }

    public function retentionDeletion(int $weeks, int $apiKey): int {
        // insuring clause
        if ($weeks < 1) {
            return 0;
        }

        $params = [
            ':api_key' => $apiKey,
            ':weeks' => $weeks,
        ];

        $query = (
            'DELETE FROM event
            WHERE
                event.key = :api_key AND
                (EXTRACT(EPOCH FROM (NOW() - event.time)) / (60 * 60 * 24 * 7)) >= :weeks'
        );

        return $this->execQuery($query, $params);
    }
}
