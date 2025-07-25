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
    'AdminApi_page_title' => 'Tracking ID',
    'AdminApi_breadcrumb_title' => 'Api',

    'AdminApi_table_title_tooltip' => 'Use the Tracking ID to access the API. Include it in the HTTP header when sending event information to the endpoint, as shown in the examples below.',

    'AdminApi_http_endpoint' => 'Tracking code',
    'AdminApi_server_language' => 'Server language',
    'AdminApi_http_endpoint_tooltip' => [
        'title' => 'This container holds your tracking code and is used to collect and process user data.',
        'items' => [
            'Choose your server language (PHP, Python, Node.js, Ruby, cURL).',
            'Replace the placeholders in the code with your specific values.',
            'Paste the completed code on every page of your website or app that you want to track. This code should be included for logged-in users.',
            'Data will appear in reports within approximately one minute.',
        ],
    ],

    'AdminApi_table_column_sensor_key' => 'Tracking ID',
    'AdminApi_table_column_created_at' => 'Created at',

    'AdminApi_table_column_action' => 'Action',
    'AdminApi_table_column_action_tooltip' => 'To renew the Tracking ID value, click the "Reset" button. Note that this action cancels the validity of the previously used key.',

    'AdminApi_table_button_reset' => 'Reset',
    'AdminApi_reset_success_message' => 'The Tracking ID has been reset successfully.',

    'AdminApi_data_enrichment_title' => 'Data enrichment',
    'AdminApi_data_enrichment_title_tooltip' => 'Choose the components of event information to enhance by additionally applying internal, external, and open-sourced data.',
    'AdminApi_data_enrichment_save_button' => 'Save',
    'AdminApi_data_enrichment_attributes' => [
        'domain' => 'Domain',
        'email' => 'Email',
        'ip' => 'IP address',
        'ua' => 'User agent',
        'phone' => 'Phone',
    ],
    'AdminApi_data_enrichment_success_message' => 'Enrichment settings have been updated successfully.',

    'AdminApi_form_title' => 'Enrichment key',
    'AdminApi_form_title_tooltip' => 'Enrichment key enables access to enrichment.',
    'AdminApi_form_button_save' => 'Save',
    'AdminApi_form_field_token_label' => 'Enrichment key',
    'AdminApi_form_field_token_placeholder' => 'TIR:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx=',
    'AdminApi_current_token_tooltip' => 'Current key: ',
    'AdminApi_form_confirmationMessage' => 'You can use a limited version of tirreno without a paid subscription or choose to enrich one or several types of data. To learn about enrichment plans and obtain a subscription key, please visit: https://www.tirreno.com/pricing/',

    'AdminApi_token_management_title' => 'Enrichment subscription management',
    'AdminApi_token_management_title_tooltip' => 'Usage statistics and subscription key management',
    'AdminApi_token_management_plan_col' => 'Plan',
    'AdminApi_token_management_subscription_status_col' => 'Status',
    'AdminApi_token_management_last_period_usage_col' => 'Current usage',
    'AdminApi_token_management_next_billed_col' => 'Next billed at',
    'AdminApi_token_management_update_payment_action' => 'Update card',
    'AdminApi_token_management_update_payment_button' => 'Update',
    'AdminApi_token_management_reset_token_button' => 'Reset',

    'AdminApi_exchange_blacklist_title' => 'Data exchange',
    'AdminApi_exchange_blacklist_title_tooltip' => 'Enable data exchange to participate in the formation and benefit from the utilization of the network alert list.',
    'AdminApi_exchange_blacklist_warning' => 'Please note that changing this parameter will only affect newly added items.',
    'AdminApi_exchange_blacklist_label' => 'Blacklisted items',
    'AdminApi_exchange_blacklist_save_button' => 'Save',
    'AdminApi_exchange_blacklist_success_message' => 'Data exchange parameter has been updated successfully.',
    'AdminApi_update_token_success_message' => 'Enrichment key has been updated successfully.',

    'AdminApi_data_alert_list_exchange' => 'Antifraud network exchange',

    'AdminApi_shared_keys_title' => 'Share access',
    'AdminApi_shared_keys_delete' => '[ x ]',
    'AdminApi_shared_keys_title_tooltip' => 'Manage operators that can use this console. To share access, start by sending an invitation email.',
    'AdminApi_shared_keys_empty' => 'You are not sharing your access with anyone else.',

    'AdminApi_add_co_owner_form_email' => 'Email',
    'AdminApi_add_co_owner_form_invite_button' => 'Invite',
    'AdminApi_add_co_owner_success_message' => 'Invitation to share access has been sent successfully.',

    'AdminApi_invitation_email_subject' => 'Invitation',
    'AdminApi_invitation_email_body' => '%s has invited you to collaborate. You can accept this invitation by setting the password for your account or decline the invitation by ignoring this email. %s This invitation will expire in 24 hours.',

    'AdminApi_remove_co_owner_success_message' => 'Co-owner has been removed successfully.',
    'AdminApi_remove_co_owner_error_message' => 'An error occured while removing co-owner.',

    'AdminApi_manual_enrichment_form_title' => 'Manual data enrichment',
    'AdminApi_manual_enrichment_form_confirmationMessage' => 'Identifies and sends data that was previously unenriched for re-enrichment in the background, ensuring all records are complete and accurate.',
    'AdminApi_manual_enrichment_form_button_submit' => 'Preview',
    'AdminApi_manual_enrichment_success_message' => 'Enrichment process started.',

    'AdminApi_manual_enrichment_popup_header' => 'Manual data enrichment',
    'AdminApi_manual_enrichment_popup_submit_button' => 'Start enrichment',
];
