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

namespace Models\Grid\Isps;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_isp.id DESC';
    protected $dateRangeField = 'event_isp.lastseen';

    protected $allowedColumns = ['asn', 'name', 'total_visit', 'total_ip', 'total_account', 'fraud', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_isp.id,
                event_isp.asn,
                event_isp.name,
                -- event_isp.description,
                event_isp.total_visit,
                event_isp.total_account,
                (
                    SELECT COUNT(DISTINCT event.account)
                    FROM event
                    LEFT JOIN event_ip ON event.ip = event_ip.id
                    LEFT JOIN event_account ON event.account = event_account.id
                    WHERE
                        event_ip.isp = event_isp.id AND
                        event.key = :api_key AND
                        event_account.fraud IS TRUE
                ) AS fraud,
                (
                    SELECT
                        COUNT ( DISTINCT eip.ip )

                    FROM
                        event_ip AS eip

                    WHERE
                        eip.isp = event_isp.id
                        AND eip.key = event_isp.key
                        AND eip.isp IS NOT NULL

                ) AS total_ip
            FROM
                event_isp

            WHERE
                event_isp.key = :api_key
                %s

            GROUP BY
                event_isp.id'
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
                COUNT (event_isp.id)

            FROM
                event_isp

            WHERE
                event_isp.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_isp.id', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(event_isp.asn::text)      LIKE LOWER(:search_value)
                    OR LOWER(event_isp.name)        LIKE LOWER(:search_value)
                    OR LOWER(event_isp.description) LIKE LOWER(:search_value)
                )'
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
