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

namespace Models\Grid\Domains;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_domain.id DESC';
    protected $dateRangeField = 'event_domain.lastseen';

    protected $allowedColumns = ['domain', 'free_email_provider', 'tranco_rank',
        'disabled', 'disposable_domains', 'creation_date', 'total_account', 'fraud', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_domain.id,
                event_domain.domain,
                event_domain.ip,
                event_domain.total_account,
                event_domain.total_visit,
                event_domain.disposable_domains,
                event_domain.creation_date,
                event_domain.disabled,
                event_domain.free_email_provider,
                event_domain.tranco_rank,
                (
                    SELECT COUNT(*)
                    FROM event_email
                    WHERE
                        event_email.domain = event_domain.id AND
                        event_email.key = :api_key AND
                        event_email.fraud_detected IS TRUE
                ) AS fraud

            FROM
                event_domain

            WHERE
                event_domain.key = :api_key
                %s

            GROUP BY
                event_domain.id'
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
                COUNT (event_domain.id)

            FROM
                event_domain

            WHERE
                event_domain.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_domain.id', $queryParams);

        if (isset($search) && $search['value'] !== null) {
            $searchConditions .= (
                ' AND (
                    LOWER(event_domain.domain)             LIKE LOWER(:search_value)
                    OR TEXT(event_domain.creation_date)    LIKE LOWER(:search_value)
                )'
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
