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

namespace Models\Grid\Payloads\FieldAuditTrail;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_field_audit_trail.id DESC';
    protected $dateRangeField = 'event_field_audit_trail.created';

    protected $allowedColumns = ['id', 'created'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_field_audit_trail.id,
                event_field_audit_trail.created,
                event_field_audit_trail.event_id,
                event_field_audit_trail.field_id,
                event_field_audit_trail.field_name,
                event_field_audit_trail.old_value,
                event_field_audit_trail.new_value,
                event_field_audit_trail.parent_id,
                event_field_audit_trail.parent_name

            FROM
                event_field_audit_trail

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
        $searchConditions = $this->injectIdQuery('event_field_audit_trail.id', $queryParams);

        //Add ids into request
        $query = sprintf($query, $searchConditions);
    }
}
