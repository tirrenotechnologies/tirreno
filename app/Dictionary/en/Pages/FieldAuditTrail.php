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

return [
    'AdminFieldAudit_table_title' => 'Fields',
    'AdminFieldAudit_table_title_tooltip' => 'Track modifications by users to important fields, including what changed and when.',
    'AdminFieldAuditTrail_search_placeholder' => 'Field ID, Name, Value, Parent',
    'AdminFieldAudit_page_title' => 'Field history',
    'AdminFieldAudits_page_title' => 'Field history',
    'AdminFieldAuditTrail_table_title' => 'Field history',
    'AdminFieldAuditTrail_table_title_tooltip' => 'Track modifications by users to important fields, including what changed and when.',
    'AdminFieldAuditTrail_table_column_audit_trail_user' => 'Trust score & email',
    'AdminFieldAuditTrail_table_column_audit_trail_user_tooltip' => 'Displays two values. The trust score on the left side is a calculated per-user value. It ranges from 0 (lowest trust) to 99 (highest trust). The value on the right side is a user email provided by a client platform.',
    'AdminFieldAuditTrail_table_column_audit_trail_created' => 'Timestamp',
    'AdminFieldAuditTrail_table_column_audit_trail_created_tooltip' => 'The date the field was created.',
    'AdminFieldAuditTrail_table_column_audit_trail_field' => 'Field',
    'AdminFieldAuditTrail_table_column_audit_trail_field_tooltip' => 'The name of the field that has been changed.',
    'AdminFieldAuditTrail_table_column_audit_trail_old_value' => 'Old value',
    'AdminFieldAuditTrail_table_column_audit_trail_old_value_tooltip' => 'Previous value of the field.',
    'AdminFieldAuditTrail_table_column_audit_trail_new_value' => 'New value',
    'AdminFieldAuditTrail_table_column_audit_trail_new_value_tooltip' => 'Updated value of the field.',
    'AdminFieldAuditTrail_table_column_audit_trail_parent' => 'Parent ID',
    'AdminFieldAuditTrail_table_column_audit_trail_parent_tooltip' => 'ID of the parent record related to the field change.',

    'AdminFieldAudit_table_column_audit_trail_field_id' => 'Field ID',
    'AdminFieldAudit_table_column_audit_trail_field_id_tooltip' => 'The ID of the field that has been changed.',

    'AdminFieldAudit_table_column_audit_trail_field_name' => 'Field name',
    'AdminFieldAudit_table_column_audit_trail_field_name_tooltip' => 'The name of the field that has been changed.',

    'AdminFieldAuditTrail_table_column_audit_user' => 'Trust score & email',
    'AdminFieldAuditTrail_table_column_audit_user_tooltip' => 'Displays two values. The trust score on the left side is a calculated per-user value. It ranges from 0 (lowest trust) to 99 (highest trust). The value on the right side is a user email provided by a client platform.',

    'AdminFieldAudit_table_column_audit_trail_lastseen' => 'Last modified',
    'AdminFieldAudit_table_column_audit_trail_lastseen_tooltip' => 'The date the field was changed.',

    'AdminFieldAudit_table_column_audit_trail_created' => 'Created',
    'AdminFieldAudit_table_column_audit_trail_created_tooltip' => 'The date the field was created.',

    'AdminFieldAudit_counters_total_users' => 'User count',
    'AdminFieldAudit_counters_total_users_tooltip' => 'The number of users performed the field edit.',
    'AdminFieldAudit_counters_total_ips' => 'IP count',
    'AdminFieldAudit_counters_total_ips_tooltip' => 'The number of IP addresses from which field edit was performed.',
    'AdminFieldAudit_counters_total_events' => 'Event count',
    'AdminFieldAudit_counters_total_events_tooltip' => 'The number of events performing the field edit.',
    'AdminFieldAudit_counters_total_isps' => 'Network count',
    'AdminFieldAudit_counters_total_isps_tooltip' => 'The number of networks from which field edit was performed.',
    'AdminFieldAudit_counters_total_edits' => 'Edit count',
    'AdminFieldAudit_counters_total_edits_tooltip' => 'The number of field edits.',
];
