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
    'api_page_title' => 'Tracking ID',
    'api_breadcrumb_title' => 'Api',

    'api_table_title_tooltip' => 'Use the Tracking ID to access the API. Include it in the HTTP header when sending event information to the endpoint, as shown in the examples below.',

    'api_http_endpoint' => 'Tracking code',
    'api_server_language' => 'Server language',
    'api_http_endpoint_data_ingestion' => [
        'Replace the placeholders in the code with your specific values;',
        'Paste the completed code into every page of your application that you want to monitor;',
        'Data will appear in tirreno dashboard within approximately one minute.',
    ],

    'api_table_column_sensor_key' => 'Tracking ID',
    'api_table_column_sensor_url' => 'Sensor URL',
    'api_table_column_created_at' => 'Created at',

    'api_table_column_action' => 'Action',
    'api_table_column_action_tooltip' => 'To renew the Tracking ID value, click the "Reset" button. Note that this action cancels the validity of the previously used key.',

    'api_table_button_reset' => 'Reset',
    'api_reset_success_message' => 'The Tracking ID has been reset successfully.',

    'api_table_column_data_ingestion' => 'Data ingestion',

    'api_data_enrichment_title' => 'Data enrichment',
    'api_data_enrichment_title_tooltip' => 'Choose the components of event information to enhance by additionally applying internal, external, and open-sourced data.',
    'api_data_enrichment_save_button' => 'Save',
    'api_data_enrichment_attributes' => [
        'domain' => 'Domain enrichment',
        'email' => 'Email enrichment',
        'ip' => 'IP address enrichment',
        'ua' => 'User agent enrichment',
        'phone' => 'Phone enrichment',
    ],
    'api_data_enrichment_success_message' => 'Enrichment settings have been updated successfully.',

    'api_form_title' => 'Enrichment key',
    'api_form_title_tooltip' => 'Enrichment key enables access to enrichment.',
    'api_form_button_save' => 'Save',
    'api_form_field_token_label' => 'Enrichment key',
    'api_form_field_token_placeholder' => 'TIR:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=',
    'api_current_token_tooltip' => 'Current key: ',
    'api_form_confirmationMessage' => 'You can use tirreno without a paid subscription or choose to enrich IP data. To learn about enrichment plans and obtain a subscription key, please visit: https://www.tirreno.com/pricing/',

    'api_token_management_title' => 'Enrichment subscription management',
    'api_token_management_title_tooltip' => 'Usage statistics and subscription key management',
    'api_token_management_plan_col' => 'Plan',
    'api_token_management_subscription_status_col' => 'Status',
    'api_token_management_last_period_usage_col' => 'Current usage',
    'api_token_management_next_billed_col' => 'Next billed at',
    'api_token_management_update_payment_action' => 'Update card',
    'api_token_management_update_payment_button' => 'Update',
    'api_token_management_reset_token_button' => 'Reset',

    'api_exchange_blacklist_title' => 'Data exchange',
    'api_exchange_blacklist_title_tooltip' => 'Enable data exchange to participate in the formation and benefit from the utilization of the network alert list.',
    'api_exchange_blacklist_warning' => 'Please note that changing this parameter will only affect newly added items.',
    'api_exchange_blacklist_label' => 'Blacklisted items',
    'api_exchange_blacklist_save_button' => 'Save',
    'api_exchange_blacklist_success_message' => 'Data exchange parameter has been updated successfully.',
    'api_update_token_success_message' => 'Enrichment key has been updated successfully.',

    'api_data_alert_list_exchange' => 'Antifraud network exchange',

    'api_shared_keys_title' => 'Share access',
    'api_shared_keys_delete' => '[ x ]',
    'api_shared_keys_title_tooltip' => 'Manage operators that can use this console. To share access, start by sending an invitation email.',
    'api_shared_keys_empty' => 'You are not sharing your access with anyone else.',

    'api_add_co_owner_form_email' => 'Email',
    'api_add_co_owner_form_invite_button' => 'Invite',
    'api_add_co_owner_success_message' => 'Invitation to share access has been sent successfully.',

    'api_invitation_email_subject' => 'Invitation',
    'api_invitation_email_body' => '%s has invited you to collaborate. You can accept this invitation by setting the password for your account or decline the invitation by ignoring this email. %s This invitation will expire in 24 hours.',

    'api_remove_co_owner_success_message' => 'Co-owner has been removed successfully.',
    'api_remove_co_owner_error_message' => 'An error occurred while removing co-owner.',

    'api_manual_enrichment_form_title' => 'Manual data enrichment',
    'api_manual_enrichment_form_confirmationMessage' => 'Finds records that were never enriched and enriches them in the background.',
    'api_manual_enrichment_form_button_submit' => 'Preview',
    'api_manual_enrichment_success_message' => 'Enrichment process started.',

    'api_manual_enrichment_popup_header' => 'Manual data enrichment',
    'api_manual_enrichment_popup_submit_button' => 'Start enrichment',

    'api_manual_reset_key_popup_warning_header' => 'Reset Tracking ID',
    'api_manual_reset_key_popup_submit_warning_message' => 'Resetting the Tracking ID will permanently replace current Tracking ID, you will immediately lose ability to use tirreno API with current Tracking ID.',
    'api_manual_reset_key_popup_submit_button' => 'Reset',
];
