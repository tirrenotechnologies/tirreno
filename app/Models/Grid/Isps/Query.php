<?php

/**
 * tirreno ~ open-source security framework
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

namespace Tirreno\Models\Grid\Isps;

class Query extends \Tirreno\Models\Grid\Base\Query {
    protected ?string $defaultOrder = 'event_isp.id DESC';
    protected string $dateRangeField = 'event_isp.lastseen';

    protected array $allowedColumns = ['asn', 'name', 'total_visit', 'total_ip', 'total_account', 'fraud', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_isp.id,
                event_isp.asn,
                event_isp.name,
                -- event_isp.description,
                event_isp.total_ip,
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
                ) AS fraud
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

        $search = \Tirreno\Utils\Conversion::getDictionaryRequestParam('search');
        $searchConditions = $this->injectIdQuery('event_isp.id', $queryParams);

        if (isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(event_isp.asn::text)      LIKE LOWER(:search_value) OR
                    LOWER(event_isp.name)           LIKE LOWER(:search_value) OR
                    LOWER(event_isp.description)    LIKE LOWER(:search_value)
                )'
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
