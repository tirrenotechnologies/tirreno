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
    'settings_page_title' => 'Settings',
    'settings_breadcrumb_title' => 'Settings',

    'settings_changePassword_form_title' => 'Change password',
    'settings_changePassword_form_title_tooltip' => 'Change your account password here. Use a strong password to prevent unauthorized access.',
    'settings_changePassword_form_field_currentPassword_label' => 'Current password',
    'settings_changePassword_form_field_currentPassword_placeholder' => 'Enter current password',
    'settings_changePassword_form_field_newPassword_label' => 'New password',
    'settings_changePassword_form_field_newPassword_placeholder' => 'Enter new password',
    'settings_changePassword_form_field_passwordConfirmation_label' => 'Confirm new password',
    'settings_changePassword_form_field_passwordConfirmation_placeholder' => 'Re-enter new password',
    'settings_changePassword_form_button_save' => 'Save',
    'settings_changePassword_success_message' => 'Your password has been successfully changed.',

    'settings_changeEmail_form_title' => 'Change email address',
    'settings_changeEmail_form_title_tooltip' => 'Change the email address for your account here. A message with instructions on how to complete the change will be sent to the new email address.',
    'settings_changeEmail_form_field_email_label' => 'Email address',
    'settings_changeEmail_form_field_email_placeholder' => 'New email address',
    'settings_changeEmail_form_button_save' => 'Update',
    'settings_changeEmail_success_message' => 'Your email has been successfully changed.',

    'settings_form_closeAccount_title' => 'Delete account',
    'settings_form_closeAccount_confirmationMessage' => 'If you wish to permanently delete this account and all its associated data, including but not limited to entities, IP addresses and events, click the button below.',
    'settings_closeAccount_form_button_save' => 'Delete this account',
    'settings_closeAccount_success_message' => 'Your account has been successfully deleted and you are unable to use it anymore.',

    'settings_checkUpdates_form_title' => 'Check for updates',
    'settings_form_checkUpdates_currentVerision' => 'Current version',
    'settings_checkUpdates_form_button' => 'Check',

    'settings_notificationPreferences_title' => 'Review queue notifications',
    'settings_notificationPreferences_title_tooltip' => 'Select how frequently email notifications should be sent.',
    'settings_notificationPreferences_reviewReminderFrequency_label' => 'Period',
    'settings_notificationPreferences_reviewReminderFrequency_options' => [
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'off' => 'Off',
    ],
    'settings_notificationPreferences_button_save' => 'Update',
    'settings_notificationPreferences_success_message' => 'Your notification preferences have been successfully updated.',

    'settings_delete_account_warning_message_par1' => 'Please note that if you choose to delete your account, you will immediately lose access, and your data will be permanently deleted, '
        . 'as outlined in our terms of service. We are unable to offer pro-rata refunds for any remaining subscription period.',
    'settings_delete_account_warning_message_par2' => 'Alternatively, if you wish to pause your subscription without permanently deleting your account, you can cancel it instead. '
        . 'Upon cancellation, you will immediately lose access, but we will securely store your account data for one year before automatic deletion. '
        . 'You can reactivate your account at any time within one year of cancellation.',


    'settings_submit_account_deletion_button' => 'Confirm account deletion',
    'settings_account_deletion_warning_header' => 'Permanent account deletion',
];
