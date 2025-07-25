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

namespace Models\Grid\Phones;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_phone.lastseen DESC';
    protected $dateRangeField = 'event_phone.lastseen';

    protected $allowedColumns = ['phonenumber', 'invalid', 'full_country', 'carrier_name', 'type', 'shared', 'fraud_detected'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_phone.id,
                event_phone.phone_number as phonenumber,
                event_phone.type,
                event_phone.carrier_name,
                event_phone.lastseen,
                event_phone.invalid,
                event_phone.shared,
                event_phone.alert_list,
                event_phone.fraud_detected,

                countries.id    AS country_id,
                countries.iso   AS country_iso,
                countries.value AS full_country

            FROM
                event_phone

            LEFT JOIN countries
            ON (event_phone.country_code = countries.id)

            WHERE
                event_phone.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);
        $this->applyOrder($query);
        $this->applyLimit($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                COUNT(*)

            FROM
                event_phone

            WHERE
                event_phone.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_phone.id', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    event_phone.phone_number      LIKE :search_value
                    OR TEXT(event_phone.lastseen) LIKE :search_value
                )'
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
