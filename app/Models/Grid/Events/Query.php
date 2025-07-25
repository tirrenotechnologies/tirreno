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

namespace Models\Grid\Events;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event.time DESC, event.id DESC';
    protected $dateRangeField = 'event.time';

    protected $allowedColumns = ['userid', 'time', 'type', 'ip', 'ip_type', 'device', 'session_id', 'time', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event.id,
                event.time,

                event_type.value AS event_type,
                event_type.name AS event_type_name,

                event_account.is_important,
                event_account.id AS accountid,
                event_account.userid AS accounttitle,
                event_account.score_updated_at,
                event_account.score,
                event_account.fraud,

                event_url.url,
                event_url.id as url_id,
                event_url_query.query,
                event_url.title,

                event_ip.ip,
                event_ip.data_center,
                event_ip.vpn,
                event_ip.tor,
                event_ip.relay,
                event_ip.starlink,
                event_ip.blocklist,
                event_ip.fraud_detected,
                event_ip.checked,

                event_isp.name AS isp_name,

                countries.iso       AS country_iso,
                countries.id        AS country_id,
                countries.value     AS full_country,

                event_ua_parsed.ua,
                event_ua_parsed.device,
                event_ua_parsed.os_name,

                event_email.email,
                event.http_code,
                event.session_id,
                event_session.total_visit AS session_cnt,
                event_session.lastseen AS session_max_t,
                event_session.created AS session_min_t,
                event_session.lastseen - event_session.created AS session_duration

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

            LEFT JOIN event_email
            ON (event.email = event_email.id)

            LEFT JOIN event_session
            ON (event.time = event_session.lastseen AND event.session_id = event_session.id)

            WHERE
                event.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);
        $this->applyEventTypes($query, $queryParams);
        $this->applyRules($query, $queryParams);
        $this->applyOrder($query);
        $this->applyLimit($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $query = null;
        $queryParams = $this->getQueryParams();

        if ($this->itemId !== null) {
            switch ($this->itemKey) {
                case 'userId':
                    $query = 'SELECT total_visit AS count FROM event_account WHERE key = :api_key AND id = :item_id';
                    break;
                case 'ispId':
                    $query = 'SELECT total_visit AS count FROM event_isp WHERE key = :api_key AND id = :item_id';
                    break;
                case 'domainId':
                    $query = 'SELECT total_visit AS count FROM event_domain WHERE key = :api_key AND id = :item_id';
                    break;
                case 'resourceId':
                    $query = 'SELECT total_visit AS count FROM event_url WHERE key = :api_key AND id = :item_id';
                    break;
                case 'countryId':
                    $query = 'SELECT total_visit AS count FROM event_country WHERE key = :api_key AND country = :item_id';
                    break;
                case 'ipId':
                    $query = 'SELECT total_visit AS count FROM event_ip WHERE key = :api_key AND id = :item_id';
                    break;
                case 'deviceId':
                    $query = (
                        'SELECT
                            COUNT(event.id) AS count
                        FROM event
                        INNER JOIN event_device
                        ON (event.device = event_device.id)
                        WHERE
                            event_device.key = :api_key AND
                            event_device.user_agent = :item_id'
                    );
                    break;
            }
        }

        if (!$query) {
            $query = (
                'SELECT
                    COUNT(event.id) AS count

                FROM
                    event

                INNER JOIN event_account
                ON (event.account = event_account.id)

                INNER JOIN event_url
                ON (event.url = event_url.id)

                INNER JOIN event_ip
                ON (event.ip = event_ip.id)

                INNER JOIN countries
                ON (event_ip.country = countries.id)

                INNER JOIN event_type
                ON (event.type = event_type.id)

                LEFT JOIN event_email
                ON (event.email = event_email.id)

                WHERE
                    event.key = :api_key
                    %s'
            );

            $this->applySearch($query, $queryParams);
            $this->applyEventTypes($query, $queryParams);
            $this->applyRules($query, $queryParams);
        }

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        //Add dates into request
        $this->applyDateRange($query, $queryParams);

        //Apply itemId into request
        $this->applyRelatedToIdSearchConitions($query);

        $searchConditions = '';
        $search = $this->f3->get('REQUEST.search');

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            //TODO: user isIp function here
            if (filter_var($search['value'], FILTER_VALIDATE_IP) !== false) {
                $searchConditions .= (
                    ' AND
                    (
                        event_ip.ip = :search_value
                    )'
                );

                $queryParams[':search_value'] = $search['value'];
            } else {
                // https://stackoverflow.com/a/63701098
                $searchConditions .= (
                    " AND
                    (
                        LOWER(event_email.email)            LIKE LOWER(:search_value) OR
                        LOWER(event_account.userid)         LIKE LOWER(:search_value) OR
                        event.http_code::text               LIKE LOWER(:search_value) OR

                        CASE WHEN event.http_code >= 400 THEN
                            CONCAT('error ', event.http_code)
                        ELSE
                            '' END                          LIKE LOWER(:search_value) OR

                        TO_CHAR(event.time::timestamp without time zone, 'dd/mm/yyyy hh24:mi:ss') LIKE :search_value
                    )"
                );

                $queryParams[':search_value'] = '%' . $search['value'] . '%';
            }
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }

    protected function getQueryParams(): array {
        $params = [':api_key' => $this->apiKey];
        if ($this->itemId !== null) {
            $params[':item_id'] = $this->itemId;
        }

        return $params;
    }

    private function applyRelatedToIdSearchConitions(string &$query): void {
        $searchConditions = null;

        if ($this->itemId !== null) {
            switch ($this->itemKey) {
                case 'userId':
                    $searchConditions = ' AND event.account = :item_id %s';
                    break;
                case 'ispId':
                    $searchConditions = ' AND event_isp.id = :item_id %s';
                    break;
                case 'domainId':
                    $searchConditions = ' AND event_email.domain = :item_id %s';
                    break;
                case 'resourceId':
                    $searchConditions = ' AND event.url = :item_id %s';
                    break;
                case 'countryId':
                    $searchConditions = ' AND countries.id = :item_id %s';
                    break;
                case 'ipId':
                    $searchConditions = ' AND event_ip.id = :item_id %s';
                    break;
                case 'deviceId':
                    $searchConditions = ' AND event_ua_parsed.id = :item_id %s';
                    break;
            }
        }

        //Add search and ids into request
        if ($searchConditions !== null) {
            $query = sprintf($query, $searchConditions);
        }
    }

    private function applyEventTypes(string &$query, array &$queryParams): void {
        $eventTypeIds = $this->f3->get('REQUEST.eventTypeIds');
        if ($eventTypeIds === null || !count($eventTypeIds)) {
            return;
        }

        $clauses = [];
        foreach ($eventTypeIds as $key => $eventTypeId) {
            $clauses[] = 'event.type = :event_type_id_' . $key;
            $queryParams[':event_type_id_' . $key] = $eventTypeId;
        }

        $query .= ' AND (' . implode(' OR ', $clauses) . ')';
    }

    private function applyRules(string &$query, array &$queryParams): void {
        $ruleUids = $this->f3->get('REQUEST.ruleUids');
        if ($ruleUids === null) {
            return;
        }

        $uids = [];
        foreach ($ruleUids as $key => $ruleUid) {
            $uids[] = ['uid' => $ruleUid];
        }

        $query .= ' AND score_details @> :rules_uids::jsonb';
        $queryParams[':rules_uids'] = json_encode($uids);
    }
}
