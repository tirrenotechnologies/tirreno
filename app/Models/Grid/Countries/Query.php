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

namespace Models\Grid\Countries;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = null;
    protected $dateRangeField = 'event_country.lastseen';

    protected $allowedColumns = ['full_country', 'country', 'total_account', 'total_visit', 'total_ip', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $joinPart = '';
        $selectPart = (
            "event_country.total_visit,
            event_country.total_account,
            event_country.total_ip"
        );

        $params = $this->f3->get('GET');
        if ($this->getDatesRange($params) !== null && !array_key_exists('draw', $params)) {
            // count total_account for dateRange; params should be applied in applyDateRange()
            $selectPart = (
                "event_country.total_visit,
                (SELECT COUNT(DISTINCT account)
                FROM event LEFT JOIN event_ip ON event.ip = event_ip.id
                WHERE
                    event_ip.country = event_country.country AND
                    event_ip.key = :api_key AND
                    event.time >= :start_time AND
                    event.time <= :end_time
                ) AS total_account,
                event_country.total_ip"
            );
        } elseif ($this->itemId !== null) {
            $selectPart = (
                "sub.total_visit,
                sub.total_account,
                sub.total_ip"
            );
            $joinPart = $this->totalQuery();
        }

        $query = (
            "SELECT
                countries.id        AS country_id,
                countries.id        AS country,
                countries.serial    AS id,
                countries.value     AS full_country,
                countries.serial,

                {$selectPart}

            FROM
                event_country

            INNER JOIN countries
            ON event_country.country = countries.serial

            {$joinPart}

            WHERE
                event_country.key = :api_key
                %s"
        );

        $this->applySearch($query, $queryParams);
        $this->applyOrder($query);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                COUNT(event_country.id)

            FROM
                event_country

            INNER JOIN countries
            ON event_country.country = countries.serial

            WHERE
                event_country.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        //Add dates into request
        $this->applyDateRange($query, $queryParams);

        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('countries.serial', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(countries.value)              LIKE LOWER(:search_value)
                    OR LOWER(countries.id)              LIKE LOWER(:search_value)
                )'
            );
            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }


    private function totalQuery(): string {
        $field = $this->getItemField();
        $join = $this->getJoinQueryPart();
        return (
            "LEFT JOIN (
                SELECT
                    event_ip.country,
                    COUNT(event.id) AS total_visit,
                    COUNT(DISTINCT event.account) AS total_account,
                    COUNT(DISTINCT event.ip) AS total_ip
                FROM
                    event
                INNER JOIN event_ip
                ON event.ip = event_ip.id
                {$join}
                WHERE
                    event.key       = :api_key AND
                    {$field}        = :item_id
                GROUP BY
                    event_ip.country
            ) sub ON countries.serial = sub.country"
        );
    }

    private function getItemField(): string {
        $field = '';
        switch ($this->itemKey) {
            case 'userId':
                $field = 'event.account';
                break;
            case 'ispId':
                $field = 'event_ip.isp';
                break;
            case 'domainId':
                $field = 'event_email.domain';
                break;
            case 'deviceId':
                $field = 'event_device.user_agent';
                break;
            case 'resourceId':
                $field = 'event.url';
                break;
        }

        return $field;
    }

    private function getJoinQueryPart(): string {
        $query = '';
        switch ($this->itemKey) {
            case 'domainId':
                $query = 'LEFT JOIN event_email ON event.email = event_email.id';
                break;
            case 'deviceId':
                $query = 'LEFT JOIN event_device ON event.device = event_device.id';
                break;
        }

        return $query;
    }

    protected function getQueryParams(): array {
        $params = [':api_key' => $this->apiKey];
        if ($this->itemId !== null) {
            $params[':item_id'] = $this->itemId;
        }

        return $params;
    }

    public function injectIdQuery(string $field, &$params): string {
        $idsQuery = $this->ids;
        if ($idsQuery === null || $idsQuery === '') {
            return '';
        }

        return " AND $field IN ($idsQuery)";
    }
}
