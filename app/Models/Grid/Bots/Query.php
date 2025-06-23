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

namespace Models\Grid\Bots;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'ed.lastseen DESC';
    protected $dateRangeField = 'ed.lastseen';

    protected $allowedColumns = ['id', 'device', 'os_name', 'modified'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_ua_parsed.id,
                event_ua_parsed.device,
                event_ua_parsed.browser_name,
                event_ua_parsed.browser_version,
                event_ua_parsed.os_name,
                event_ua_parsed.os_version,
                event_ua_parsed.ua,
                event_ua_parsed.modified,
                ed.lastseen

            FROM
                event_ua_parsed

            LEFT JOIN (
                SELECT
                    user_agent,
                    MAX(lastseen) AS lastseen
                FROM event_device
                WHERE key = :api_key
                GROUP BY user_agent
            ) AS ed
            ON event_ua_parsed.id = ed.user_agent

            WHERE
                event_ua_parsed.key = :api_key AND
                event_ua_parsed.modified IS TRUE
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
                event_ua_parsed

            LEFT JOIN (
                SELECT
                    user_agent,
                    MAX(lastseen) AS lastseen
                FROM event_device
                WHERE key = :api_key
                GROUP BY user_agent
            ) AS ed
            ON event_ua_parsed.id = ed.user_agent

            WHERE
                event_ua_parsed.key = :api_key AND
                event_ua_parsed.modified IS TRUE
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $searchConditions = '';
        $search = $this->f3->get('REQUEST.search');

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions = (
                ' AND
                (
                    event_ua_parsed.device          LIKE LOWER(:search_value) OR
                    event_ua_parsed.browser_name    LIKE LOWER(:search_value) OR
                    event_ua_parsed.os_name         LIKE LOWER(:search_value) OR
                    event_ua_parsed.ua              LIKE LOWER(:search_value)
                )'
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search into request
        $query = sprintf($query, $searchConditions);
    }
}
