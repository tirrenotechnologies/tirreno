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

class Ids extends \Models\Grid\Base\Ids {
    public function getDataIdsByUserId(): string {
        return (
            'SELECT
                event_field_audit_trail.id AS itemid
            FROM event_field_audit_trail
            WHERE
                event_field_audit_trail.key = :api_key AND
                event_field_audit_trail.account_id = :account_id'
        );
    }
}
