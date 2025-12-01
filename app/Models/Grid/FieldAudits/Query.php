<?php

/**
 * tirreno ~ open security analytics
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

namespace Models\Grid\FieldAudits;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_field_audit.id DESC';
    protected $dateRangeField = 'event_field_audit.lastseen';

    protected $allowedColumns = ['id', 'created', 'field_id', 'field_name', 'lastseen'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_field_audit.id,
                event_field_audit.id AS field_audit_id,
                event_field_audit.created,
                event_field_audit.lastseen,
                event_field_audit.field_id,
                event_field_audit.field_name,
                event_field_audit.total_account,
                event_field_audit.total_visit,
                event_field_audit.total_edit

            FROM
                event_field_audit

            WHERE
                event_field_audit.key = :api_key
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
                event_field_audit

            WHERE
                event_field_audit.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $searchConditions = $this->injectIdQuery('event_field_audit.id', $queryParams);
        $search = \Utils\Conversion::getArrayRequestParam('search');

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(event_field_audit.field_id)     LIKE LOWER(:search_value) OR
                    LOWER(event_field_audit.field_name)   LIKE LOWER(:search_value)
                )'
            );
            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add ids into request
        $query = sprintf($query, $searchConditions);
    }
}
