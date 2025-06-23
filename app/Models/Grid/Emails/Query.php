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

namespace Models\Grid\Emails;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_email.lastseen DESC';
    protected $dateRangeField = 'event_email.lastseen';

    protected $allowedColumns = ['email', 'reputation', 'free_email_provider', 'data_breach',
        'data_breaches', 'disposable_domains', 'blockemails', 'fraud_detected'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_email.id,
                event_email.email,
                event_email.data_breach,
                event_email.data_breaches,
                event_email.fraud_detected,
                -- event_email.profiles,
                event_email.blockemails,
                event_email.lastseen,
                event_email.alert_list,

                event_domain.domain,
                event_domain.id AS domain_id,
                event_domain.free_email_provider,
                event_domain.disposable_domains

            FROM
                event_email

            LEFT JOIN event_domain
            ON (event_email.domain = event_domain.id)

            WHERE
                event_email.key = :api_key
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
                event_email

            WHERE
                event_email.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_email.id', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    event_email.email             LIKE :search_value
                    OR TEXT(event_email.lastseen) LIKE :search_value
                )'
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }
}
