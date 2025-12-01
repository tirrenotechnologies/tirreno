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

namespace Models\Grid\FieldAuditTrail;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_field_audit_trail.id DESC';
    protected $dateRangeField = 'event_field_audit_trail.created';

    protected $allowedColumns = ['id', 'created'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_field_audit_trail.id,
                event_field_audit_trail.account_id,
                event_field_audit_trail.created,
                event_field_audit_trail.event_id,
                event_field_audit_trail.field_name,
                event_field_audit_trail.old_value,
                event_field_audit_trail.new_value,
                event_field_audit_trail.parent_id,
                event_field_audit_trail.parent_name,

                event_field_audit.id AS field_audit_id,
                event_field_audit.field_id,

                event_account.is_important,
                event_account.id AS accountid,
                event_account.userid AS accounttitle,
                event_account.score_updated_at,
                event_account.score,
                event_account.fraud

            FROM
                event_field_audit_trail

            LEFT JOIN event_account
            ON (event_field_audit_trail.account_id = event_account.id)

            LEFT JOIN event_field_audit
            ON (event_field_audit_trail.field_id = event_field_audit.id)

            WHERE
                event_field_audit_trail.key = :api_key
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
                event_field_audit_trail

            WHERE
                event_field_audit_trail.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $searchConditions = $this->injectIdQuery('event_field_audit_trail.id', $queryParams);
        $search = \Utils\Conversion::getArrayRequestParam('search');

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(event_field_audit_trail.field_id)     LIKE LOWER(:search_value) OR
                    LOWER(event_field_audit_trail.field_name)   LIKE LOWER(:search_value) OR
                    LOWER(event_field_audit_trail.old_value)    LIKE LOWER(:search_value) OR
                    LOWER(event_field_audit_trail.new_value)    LIKE LOWER(:search_value) OR
                    LOWER(event_field_audit_trail.parent_id)    LIKE LOWER(:search_value) OR
                    LOWER(event_field_audit_trail.parent_name)  LIKE LOWER(:search_value)
                )'
            );
            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add ids into request
        $query = sprintf($query, $searchConditions);
    }
}
