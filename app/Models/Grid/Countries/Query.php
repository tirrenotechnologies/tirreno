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

        $query = (
            'SELECT
                countries.iso       AS country_iso,
                countries.id        AS country_id,
                countries.value     AS full_country,
                countries.id,

                event_country.total_visit,
                event_country.total_account,
                event_country.total_ip


            FROM
                event_country

            LEFT JOIN countries
            ON event_country.country = countries.id

            WHERE
                event_country.key = :api_key
                %s'
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
            ON event_country.country = countries.id

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
        $searchConditions = '';

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(countries.value)              LIKE LOWER(:search_value)
                    OR LOWER(countries.iso)             LIKE LOWER(:search_value)
                )'
            );
            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
