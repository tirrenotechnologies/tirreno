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

namespace Models;

class FieldAuditTrail extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_field_audit_trail';

    public function getByEventId(int $eventId, int $apiKey): array {
        $params = [
            ':event_id' => $eventId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                event_field_audit_trail.field_id,
                event_field_audit_trail.field_name,
                event_field_audit_trail.old_value,
                event_field_audit_trail.new_value,
                event_field_audit_trail.parent_id,
                event_field_audit_trail.parent_name
            FROM
                event_field_audit_trail
            WHERE
                event_field_audit_trail.event_id = :event_id AND
                event_field_audit_trail.key = :api_key

            ORDER BY id DESC'
        );

        return $this->execQuery($query, $params);
    }

    public function getByUserId(int $userId, int $apiKey): array {
        $params = [
            ':user_id' => $userId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                event_field_audit_trail.field_id,
                event_field_audit_trail.field_name,
                event_field_audit_trail.old_value,
                event_field_audit_trail.new_value,
                event_field_audit_trail.parent_id,
                event_field_audit_trail.parent_name
            FROM
                event_field_audit_trail
            WHERE
                event_field_audit_trail.account_id = :user_id AND
                event_field_audit_trail.key = :api_key

            ORDER BY id DESC'
        );

        return $this->execQuery($query, $params);
    }
}
