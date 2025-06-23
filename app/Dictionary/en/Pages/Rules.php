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

return [
    'AdminRules_page_title' => 'Rules',
    'AdminRules_breadcrumb_title' => 'Rules',
    'AdminRules_search_placeholder' => 'Rule Code, Type or Description',

    'AdminRules_table_title' => 'Rules',
    'AdminRules_table_title_tooltip' => 'This page lists conditions (rules) that can be utilized by the rules engine, which is responsible for the user trust score calculations. The page also provides a way for manually triggering a rule and getting a list of users matching it.',

    'AdminRules_table_header_code' => 'Code',
    'AdminRules_table_header_code_tooltip' => 'A rule’s code identifier.',
    'AdminRules_table_header_group' => 'Type',
    'AdminRules_table_header_group_tooltip' => 'A group to which a rule belongs.',
    'AdminRules_table_header_description' => 'Description',
    'AdminRules_table_header_description_tooltip' => 'Description of a rule.',
    'AdminRules_table_header_proportion' => 'Match rate',
    'AdminRules_table_header_proportion_tooltip' => 'The proportion between users matching the rule and all users.',
    'AdminRules_table_header_weight' => 'Weight',
    'AdminRules_table_header_weight_tooltip' => 'To enable the processing of a rule by the rules engine, set the weight value. The higher the rule’s weight, the more it influences a calculated user trust score. To save an adjusted weight value, click the red button shown on the right side.',
    'AdminRules_table_header_users' => 'Action',
    'AdminRules_table_header_users_tooltip' => 'Get a list of users matching the rule by clicking a green button; the result will be shown below the rule’s definition. When a weight value is changed, this column outputs a red button for saving an adjusted value.',

    'AdminRules_weight_minus20' => 'Positive',
    'AdminRules_weight_0' => 'None',
    'AdminRules_weight_10' => 'Medium',
    'AdminRules_weight_20' => 'High',
    'AdminRules_weight_70' => 'Extreme',

    'AdminRules_reload_rules' => 'Refresh',
    'AdminRules_reload_rules_warning' => 'Click to upload new local rules.',

    'AdminThresholdValues_form_title' => 'Thresholds settings',
    'AdminThresholdValues_form_title_tooltip' => 'Manage and set thresholds for review queue and automated user blacklisting.',
    'AdminThresholdValues_form_field_warning' => 'Set the threshold for user scores to trigger manual review or automatic blacklisting. Use auto-blacklisting with caution and only when truly necessary, as it could negatively impact the user experience due to potential misconfigurations.',
    'AdminThresholdValues_form_field_blacklist_threshold_label' => 'Auto-block',
    'AdminThresholdValues_form_field_review_queue_threshold_label' => 'Manual review queue',
    'AdminThresholdValues_form_button_save' => 'Update',
    'AdminThresholdValues_form_value_prefix' => 'Score below',
    'AdminThresholdValues_form_value_zero_prefix' => 'Score is',
    'AdminThresholdValues_update_success_message' => 'Thresholds updated successfully',
];
