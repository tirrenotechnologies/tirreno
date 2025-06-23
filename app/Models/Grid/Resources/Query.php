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

namespace Models\Grid\Resources;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_url.id DESC';
    protected $dateRangeField = 'event_url.lastseen';

    protected $allowedColumns = ['title', 'http_code', 'total_account', 'total_country', 'total_ip', 'total_visit', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_url.id,
                event_url.id AS url_id,
                event_url.key,
                event_url.url,
                event_url.title,
                event_url.http_code,

                event_url.total_visit,
                event_url.total_ip,
                event_url.total_account,
                event_url.total_country

            FROM
                event_url

            WHERE
                event_url.key = :api_key
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
                COUNT(event_url.id)

            FROM
                event_url

            WHERE
                event_url.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_url.id', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(event_url.title)      LIKE LOWER(:search_value)
                    OR LOWER(event_url.url)     LIKE LOWER(:search_value)
                )'
            );
            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
