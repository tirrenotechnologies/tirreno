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

return [
    'fieldAudit_table_title' => 'Fields',
    'fieldAudit_table_title_tooltip' => 'Track modifications by entities to important fields, including what changed and when.',
    'fieldAudits_search_placeholder' => 'Field ID, Name, Value, Parent',
    'fieldAudit_page_title' => 'Field history',
    'fieldAudits_page_title' => 'Field history',
    'fieldAuditTrail_table_title' => 'Field history',
    'fieldAuditTrail_table_title_tooltip' => 'Track modifications by entities to important fields, including what changed and when.',
    'fieldAuditTrail_table_column_audit_trail_user' => 'Trust score & email',
    'fieldAuditTrail_table_column_audit_trail_user_tooltip' => 'Displays two values. The trust score on the left side is a calculated per-user value. It ranges from 0 (lowest trust) to 99 (highest trust). The value on the right side is an entity email provided by a client platform.',
    'fieldAuditTrail_table_column_audit_trail_created' => 'Timestamp',
    'fieldAuditTrail_table_column_audit_trail_created_tooltip' => 'The date the field was created.',
    'fieldAuditTrail_table_column_audit_trail_field' => 'Field',
    'fieldAuditTrail_table_column_audit_trail_field_tooltip' => 'The name of the field that has been changed.',
    'fieldAuditTrail_table_column_audit_trail_old_value' => 'Old value',
    'fieldAuditTrail_table_column_audit_trail_old_value_tooltip' => 'Previous value of the field.',
    'fieldAuditTrail_table_column_audit_trail_new_value' => 'New value',
    'fieldAuditTrail_table_column_audit_trail_new_value_tooltip' => 'Updated value of the field.',
    'fieldAuditTrail_table_column_audit_trail_parent' => 'Parent ID',
    'fieldAuditTrail_table_column_audit_trail_parent_tooltip' => 'ID of the parent record related to the field change.',

    'fieldAudit_table_column_audit_trail_field_id' => 'Field ID',
    'fieldAudit_table_column_audit_trail_field_id_tooltip' => 'The ID of the field that has been changed.',

    'fieldAudit_table_column_audit_trail_field_name' => 'Field name',
    'fieldAudit_table_column_audit_trail_field_name_tooltip' => 'The name of the field that has been changed.',

    'fieldAuditTrail_table_column_audit_user' => 'Trust score & email',
    'fieldAuditTrail_table_column_audit_user_tooltip' => 'Displays two values. The trust score on the left side is a calculated per-user value. It ranges from 0 (lowest trust) to 99 (highest trust). The value on the right side is an entity email provided by a client platform.',

    'fieldAudit_table_column_audit_trail_lastseen' => 'Last modified',
    'fieldAudit_table_column_audit_trail_lastseen_tooltip' => 'The date the field was changed.',

    'fieldAudit_table_column_audit_trail_created' => 'Created',
    'fieldAudit_table_column_audit_trail_created_tooltip' => 'The date the field was created.',

    'fieldAudit_counters_total_users' => 'Entity count',
    'fieldAudit_counters_total_users_tooltip' => 'The number of entities that performed the field edit.',
    'fieldAudit_counters_total_ips' => 'IP count',
    'fieldAudit_counters_total_ips_tooltip' => 'The number of IP addresses from which field edit was performed.',
    'fieldAudit_counters_total_events' => 'Event count',
    'fieldAudit_counters_total_events_tooltip' => 'The number of events performing the field edit.',
    'fieldAudit_counters_total_isps' => 'Network count',
    'fieldAudit_counters_total_isps_tooltip' => 'The number of networks from which field edit was performed.',
    'fieldAudit_counters_total_edits' => 'Edit count',
    'fieldAudit_counters_total_edits_tooltip' => 'The number of field edits.',
];
