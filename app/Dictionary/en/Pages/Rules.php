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
    'rules_page_title' => 'Rules',
    'rules_breadcrumb_title' => 'Rules',
    'rules_search_placeholder' => 'Rule Code, Category or Description',

    'rules_table_title' => 'Rules',
    'rules_table_title_tooltip' => 'This page lists conditions (rules) that can be utilized by the rules engine, which is responsible for the entity trust score calculations. The page also provides a way for manually triggering a rule and getting a list of entities matching it.',

    'rules_table_header_code' => 'Code',
    'rules_table_header_code_tooltip' => 'A rule\'s code identifier.',
    'rules_table_header_group' => 'Rule category',
    'rules_table_header_group_tooltip' => 'A group to which a rule belongs.',
    'rules_table_header_description' => 'Description',
    'rules_table_header_description_tooltip' => 'Description of a rule.',
    'rules_table_header_proportion' => 'Match rate',
    'rules_table_header_proportion_tooltip' => 'The percentage of entities matching this rule among the last 1,000.',
    'rules_table_header_weight' => 'Weight',
    'rules_table_header_weight_tooltip' => 'To enable the processing of a rule by the rules engine, set the weight value. The higher the rule\'s weight, the more it influences a calculated entity trust score. To save an adjusted weight value, click the red button shown on the right side.',
    'rules_table_header_users' => 'Action',
    'rules_table_header_users_tooltip' => 'Get a list of entities matching the rule by clicking a green button; the result will be shown below the rule\'s definition. When a weight value is changed, this column outputs a red button for saving an adjusted value.',

    'rules_weight_minus20' => 'Positive',
    'rules_weight_0' => 'None',
    'rules_weight_10' => 'Medium',
    'rules_weight_20' => 'High',
    'rules_weight_70' => 'Extreme',

    'rules_reload_rules' => 'Refresh',
    'rules_reload_rules_warning' => 'Click to upload new local rules.',

    'rules_thresholdValues_form_title' => 'Thresholds settings',
    'rules_thresholdValues_form_title_tooltip' => 'Manage and set thresholds for review queue and automated entity blacklisting.',
    'rules_thresholdValues_form_field_blacklist_threshold_label' => 'Auto-blacklisting (below this score)',
    'rules_thresholdValues_form_field_review_queue_threshold_label' => 'Manual review (below this score)',
    'rules_thresholdValues_form_button_save' => 'Update',
    'rules_thresholdValues_form_value_prefix' => 'Score below',
    'rules_thresholdValues_form_value_zero_prefix' => 'Score is',
    'rules_thresholdValues_update_success_message' => 'Thresholds updated successfully',
    'rules_thresholdValues_form_field_blacklist_warning' => 'Use auto-blacklisting threshold with prior testing, and only where truly necessary.',

    'rules_thresholdValues_form_review_queue_threshold_placeholder' => 'Review queue threshold',
    'rules_thresholdValues_form_blacklist_threshold_placeholder' => 'Off',

    'rules_applyRulesPresets_form_title' => 'Rules settings reset',
    'rules_applyRulesPresets_form_title_tooltip' => 'Apply ready to use set of rules weights suiting your purposes.',
    'rules_applyRulesPresets_form_field_label' => 'Select preset',
    'rules_applyRulesPresets_form_button_save' => 'Reset',
    'rules_applyRulesPresets_success_message' => 'Rules preset applied successfully',
    'rules_applyRulesPresets_form_disabled_option' => 'None',

    'rules_applyRulesPresets_form_field_warning' => 'This is an irreversible action that will override all current rules settings.',

    'rules_applyRulesSets_selector_field_label' => 'Rules set',

    'rules_apply_rules_preset_warning_header' => 'Apply rules preset',
    'rules_apply_rules_preset_warning_message' => 'Applying this preset will permanently replace all current rule weights. This action cannot be undone.',
    'rules_submit_apply_rules_preset_button' => 'Reset rules',
];
