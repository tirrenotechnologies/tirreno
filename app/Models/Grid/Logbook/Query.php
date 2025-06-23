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

namespace Models\Grid\Logbook;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_logbook.error_type DESC, event_logbook.id DESC';
    protected $dateRangeField = 'event_logbook.raw_time';

    protected $allowedColumns = ['ip', 'raw_time', 'error_type', 'error_text'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_logbook.id,
                event_logbook.ip,
                event_logbook.error_type,
                event_logbook.error_text,
                event_logbook.raw,
                event_logbook.raw_time,
                event_error_type.name           AS error_name,
                event_error_type.value          AS error_value

            FROM
                event_logbook

            LEFT JOIN event_error_type
            ON (event_logbook.error_type = event_error_type.id)

            WHERE
                event_logbook.key = :api_key
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
                COUNT(event_logbook.id) AS count

            FROM
                event_logbook

            LEFT JOIN event_error_type
            ON (event_logbook.error_type = event_error_type.id)

            WHERE
                event_logbook.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $searchConditions = '';
        $search = $this->f3->get('REQUEST.search');

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            //TODO: user isIp function here
            if (filter_var($search['value'], FILTER_VALIDATE_IP) !== false) {
                $searchConditions .= (
                    ' AND
                    (
                        event_logbook.ip = :search_value
                    )'
                );

                $queryParams[':search_value'] = $search['value'];
            } else {
                $searchConditions .= (
                    ' AND
                    (
                        LOWER(event_logbook.raw::text)      LIKE LOWER(:search_value) OR
                        LOWER(event_logbook.error_text)     LIKE LOWER(:search_value) OR
                        LOWER(event_error_type.name)        LIKE LOWER(:search_value)
                    )'
                );

                $queryParams[':search_value'] = '%' . $search['value'] . '%';
            }
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
