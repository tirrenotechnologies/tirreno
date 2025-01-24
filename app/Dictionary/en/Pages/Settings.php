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
    'AdminSettings_page_title' => 'Settings',
    'AdminSettings_breadcrumb_title' => 'Settings',

    'AdminSettings_changePassword_form_title' => 'Password',
    'AdminSettings_changePassword_form_title_tooltip' => 'Change your account password here. Use a strong password to prevent unauthorized access.',
    'AdminSettings_changePassword_form_field_currentPassword_label' => 'Current password',
    'AdminSettings_changePassword_form_field_currentPassword_placeholder' => 'Enter current password',
    'AdminSettings_changePassword_form_field_newPassword_label' => 'New password',
    'AdminSettings_changePassword_form_field_newPassword_placeholder' => 'Enter new password',
    'AdminSettings_changePassword_form_field_passwordConfirmation_label' => 'Confirm new password',
    'AdminSettings_changePassword_form_field_passwordConfirmation_placeholder' => 'Re-enter new password',
    'AdminSettings_changePassword_form_button_save' => 'Save',
    'AdminSettings_changePassword_success_message' => 'Your password has been successfully changed.',

    'AdminSettings_changeEmail_form_title' => 'Change email address',
    'AdminSettings_changeEmail_form_title_tooltip' => 'Change the email address for your account here. A message with instructions on how to complete the change will be sent to the new email address.',
    'AdminSettings_changeEmail_form_field_email_label' => 'Email address',
    'AdminSettings_changeEmail_form_field_email_placeholder' => 'New email address',
    'AdminSettings_changeEmail_form_button_save' => 'Change email',
    'AdminSettings_changeEmail_success_message' => 'We have emailed you instructions on how to complete your email address change.',
    'AdminSettings_changeEmail_approval_pending' => 'Confirm your new email address (%s) in order to complete your email address change.',

    'AdminSettings_form_closeAccount_title' => 'Delete account',
    'AdminSettings_form_closeAccount_confirmationMessage' => 'If you wish to permanently delete this account and all its associated data, including but not limited to users, IP addresses and events, click the button below.',
    'AdminSettings_closeAccount_form_button_save' => 'Delete this account',
    'AdminSettings_closeAccount_success_message' => 'Your account has been successfully deleted and you are unable to use it anymore.',

    'AdminSettings_checkUpdates_form_title' => 'Check for updates',
    'AdminSettings_form_checkUpdates_confirmationMessage' => 'Periodically, Tirreno releases updates which can include application updates and important security patches.',
    'AdminSettings_form_checkUpdates_currentVerision' => 'Current version: ',
    'AdminSettings_checkUpdates_form_button' => 'Check',

    'AdminSettings_notificationPreferences_title' => 'Review queue notifications',
    'AdminSettings_notificationPreferences_title_tooltip' => 'Select how frequently email notifications should be sent.',
    'AdminSettings_notificationPreferences_reviewReminderFrequency_label' => 'Sending',
    'AdminSettings_notificationPreferences_reviewReminderFrequency_options' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'off' => 'Off',
    ],
    'AdminSettings_notificationPreferences_button_save' => 'Save',
    'AdminSettings_notificationPreferences_success_message' => 'Your notification preferences have been successfully updated.',

    'AdminSettings_delete_account_warning_message_par1' => 'Please note that if you choose to delete your account, you will immediately lose access, and your data will be permanently deleted, '
        . 'as outlined in our terms of service. We are unable to offer pro-rata refunds for any remaining subscription period.',
    'AdminSettings_delete_account_warning_message_par2' => 'Alternatively, if you wish to pause your subscription without permanently deleting your account, you can cancel it instead. '
        . 'Upon cancellation, you will immediately lose access, but we will securely store your account data for one year before automatic deletion. '
        . 'You can reactivate your account at any time within one year of cancellation.',


    'AdminSettings_submit_account_deletion_button' => 'Confirm account deletion',
    'AdminSettings_account_deletion_warning_header' => 'Permanent account deletion',
];
