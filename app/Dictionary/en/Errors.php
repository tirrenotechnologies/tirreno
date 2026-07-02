<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information    => please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

$base = tirreno('storage')->get('BASE');
$errors = [];
$baseErrors = [
    'email_subject'         => 'Error %s occurred',
    'email_body_template'   => (
        '<p>Error occurred at: %s</p>
        <p>Host: %s</p>
        <p>Message: </p>%s
        <p>Trace: </p>%s
        '
    ),
    '404'                   => 'Page not found',
    '500'                   => 'This function does not work right now',
    tirreno('utils')->errorCodes->CSRF_ATTACK_DETECTED             => 'We can\'t proceed with this request. Please reload the page and try again',
    tirreno('utils')->errorCodes->EMAIL_DOES_NOT_EXIST             => 'Email does not exist',
    tirreno('utils')->errorCodes->EMAIL_IS_NOT_CORRECT             => 'Email is incorrect',
    tirreno('utils')->errorCodes->EMAIL_ALREADY_EXIST              => 'Email already exists',
    tirreno('utils')->errorCodes->PASSWORD_DOES_NOT_EXIST          => 'Password does not exist',
    tirreno('utils')->errorCodes->PASSWORD_IS_TOO_SHORT            => 'Minimum password length is 8 characters',
    tirreno('utils')->errorCodes->ACCOUNT_CREATED                  => 'Thanks for your registration. Please <a href="' . $base . '/login">login</a> with your new credentials.',
    tirreno('utils')->errorCodes->INSTALL_DIR_EXISTS               => 'Please delete /install folder before continue',

    tirreno('utils')->errorCodes->ACTIVATION_KEY_DOES_NOT_EXIST    => 'Activation key does not exist',
    tirreno('utils')->errorCodes->ACTIVATION_KEY_IS_NOT_CORRECT    => 'Activation key is incorrect',
    tirreno('utils')->errorCodes->EMAIL_OR_PASSWORD_IS_NOT_CORRECT => 'Error: Permission denied.',

    tirreno('utils')->errorCodes->API_KEY_ID_DOESNT_EXIST          => 'API key does not exist',
    tirreno('utils')->errorCodes->API_KEY_ID_INVALID               => 'Incorrect Tracking ID',
    tirreno('utils')->errorCodes->OPERATOR_ID_DOES_NOT_EXIST       => 'Operator ID does not exist',
    tirreno('utils')->errorCodes->OPERATOR_IS_NOT_A_CO_OWNER       => 'Operator is not a co-owner of this Tracking ID',
    tirreno('utils')->errorCodes->UNKNOWN_ENRICHMENT_ATTRIBUTES    => 'Unknown event attributes for data enrichment',
    tirreno('utils')->errorCodes->INVALID_API_RESPONSE             => 'Unexpected API response',

    tirreno('utils')->errorCodes->FIRST_NAME_DOES_NOT_EXIST        => 'First name is a mandatory field',
    tirreno('utils')->errorCodes->LAST_NAME_DOES_NOT_EXIST         => 'Last name is a mandatory field',
    tirreno('utils')->errorCodes->COUNTRY_DOES_NOT_EXIST           => 'Country is a mandatory field',
    tirreno('utils')->errorCodes->STREET_DOES_NOT_EXIST            => 'Street address is a mandatory field',
    tirreno('utils')->errorCodes->CITY_DOES_NOT_EXIST              => 'City is a mandatory field',
    tirreno('utils')->errorCodes->STATE_DOES_NOT_EXIST             => 'State is a mandatory field',
    tirreno('utils')->errorCodes->ZIP_DOES_NOT_EXIST               => 'ZIP is a mandatory field',
    tirreno('utils')->errorCodes->TIME_ZONE_DOES_NOT_EXIST         => 'Time zone is a mandatory field',
    tirreno('utils')->errorCodes->RETENTION_POLICY_DOES_NOT_EXIST  => 'Retention policy is a mandatory field',
    tirreno('utils')->errorCodes->INVALID_REMINDER_FREQUENCY       => 'Unreviewed items reminder frequency is a mandatory field',

    tirreno('utils')->errorCodes->CURRENT_PASSWORD_DOES_NOT_EXIST  => 'Current password is a mandatory field',
    tirreno('utils')->errorCodes->CURRENT_PASSWORD_IS_NOT_CORRECT  => 'Current password is incorrect',
    tirreno('utils')->errorCodes->NEW_PASSWORD_DOES_NOT_EXIST      => 'New password is a mandatory field',
    tirreno('utils')->errorCodes->PASSWORD_CONFIRMATION_MISSING    => 'Password confirmation is a mandatory field',
    tirreno('utils')->errorCodes->PASSWORDS_ARE_NOT_EQUAL          => 'New password and password confirmation do not match',
    tirreno('utils')->errorCodes->EMAIL_IS_NOT_NEW                 => 'The new email address is the same as the current one',

    tirreno('utils')->errorCodes->RENEW_KEY_CREATED                => 'We sent you an email with further instructions on how to reset your password',
    tirreno('utils')->errorCodes->RENEW_KEY_IS_NOT_CORRECT         => 'Renew key is incorrect  ¯\_ (ツ)_/¯',
    tirreno('utils')->errorCodes->RENEW_KEY_DOES_NOT_EXIST         => 'Renew key does not exist',
    tirreno('utils')->errorCodes->RENEW_KEY_WAS_EXPIRED            => 'Renew key has expired',
    tirreno('utils')->errorCodes->ACCOUNT_ACTIVATED                => 'Your password has been successfully changed. Please <a href="' . $base . '/login">login</a> with your new credentials and continue using the system.',

    tirreno('utils')->errorCodes->THERE_ARE_NO_EVENTS_YET          => 'No events from your application have been received yet',
    tirreno('utils')->errorCodes->THERE_ARE_NO_EVENTS_LAST_DAY     => 'There are no events from your application for more than 24 hours',
    tirreno('utils')->errorCodes->OPERATION_NOT_PERMITTED          => 'Operation is not permitted',

    tirreno('utils')->errorCodes->USER_ADDED_TO_REVIEW             => 'Entity has been successfully added to review queue',
    tirreno('utils')->errorCodes->USER_ADDED_TO_WATCHLIST          => 'Entity has been successfully added to the watchlist',
    tirreno('utils')->errorCodes->USER_REMOVED_FROM_WATCHLIST      => 'Entity has been successfully removed from the watchlist',
    tirreno('utils')->errorCodes->USER_FRAUD_FLAG_SET              => 'Entity has been successfully marked as fraud',
    tirreno('utils')->errorCodes->USER_FRAUD_FLAG_UNSET            => 'Entity has been successfully marked as not fraud',
    tirreno('utils')->errorCodes->USER_REVIEWED_FLAG_SET           => 'Entity has been successfully marked as reviewed',
    tirreno('utils')->errorCodes->USER_REVIEWED_FLAG_UNSET         => 'Entity has been successfully marked as not reviewed',
    tirreno('utils')->errorCodes->USER_DELETION_FAILED             => 'Entity deletion was unsuccessful.',
    tirreno('utils')->errorCodes->USER_BLACKLISTING_FAILED         => 'Entity blacklisting was unsuccessful.',
    tirreno('utils')->errorCodes->USER_BLACKLISTING_QUEUED         => 'This entity and all associated IPs are currently queued for blacklisting.',

    tirreno('utils')->errorCodes->CHANGE_EMAIL_KEY_DOES_NOT_EXIST  => 'Change email key does not exist',
    tirreno('utils')->errorCodes->CHANGE_EMAIL_KEY_IS_NOT_CORRECT  => 'Change email key is incorrect',
    tirreno('utils')->errorCodes->CHANGE_EMAIL_KEY_WAS_EXPIRED     => 'Change email key has expired',
    tirreno('utils')->errorCodes->EMAIL_CHANGED                    => 'Your email has been successfully changed. Please <a href="' . $base . '/login">login</a> with your new credentials and continue using the system.',
    tirreno('utils')->errorCodes->RULES_SUCCESSFULLY_UPDATED       => 'Rules have been successfully updated',
    tirreno('utils')->errorCodes->INVALID_BLACKLIST_THRESHOLD      => 'Blacklist threshold is a mandatory field.',
    tirreno('utils')->errorCodes->INVALID_REVIEW_QUEUE_THRESHOLD   => 'Review queue threshold is a mandatory field.',
    tirreno('utils')->errorCodes->INVALID_THRESHOLDS_COMBINATION   => 'Blacklist threshold must not exceed review queue threshold.',
    tirreno('utils')->errorCodes->INVALID_RULES_PRESET_ID          => 'Invalid rules preset ID.',

    tirreno('utils')->errorCodes->REST_API_KEY_DOES_NOT_EXIST      => 'API key could not be found in the headers',
    tirreno('utils')->errorCodes->REST_API_KEY_IS_NOT_CORRECT      => 'API key is incorrect',
    tirreno('utils')->errorCodes->REST_API_NOT_AUTHORIZED          => 'Not authorized to perform this action',
    tirreno('utils')->errorCodes->REST_API_MISSING_PARAMETER       => 'Missing required parameter',
    tirreno('utils')->errorCodes->REST_API_VALIDATION_ERROR        => 'Validation error',
    tirreno('utils')->errorCodes->REST_API_USER_ALREADY_DELETING   => 'Entity already scheduled for deletion',
    tirreno('utils')->errorCodes->REST_API_USER_ADDED_FOR_DELETION => 'Entity added to deletion queue',

    tirreno('utils')->errorCodes->ENRICHMENT_API_KEY_NOT_EXISTS    => 'Enrichment API key is not set',
    tirreno('utils')->errorCodes->TYPE_DOES_NOT_EXIST              => 'Type is a mandatory field',
    tirreno('utils')->errorCodes->SEARCH_QUERY_DOES_NOT_EXIST      => 'Search query is a mandatory field',
    tirreno('utils')->errorCodes->ENRICHMENT_API_UNKNOWN_ERROR     => 'Unknown error occurred while processing your request',
    tirreno('utils')->errorCodes->ENRICHMENT_API_BOGON_IP          => 'IP is bogon',
    tirreno('utils')->errorCodes->ENRICHMENT_API_IP_NOT_FOUND      => 'IP not found',
    tirreno('utils')->errorCodes->RISK_SCORE_UPDATE_UNKNOWN_ERROR  => 'Unknown error occurred while processing your request',
    tirreno('utils')->errorCodes->ENRICHMENT_API_KEY_OVERUSE       => 'You\'ve used up your Enrichment API quota. Please update your <a href="' . $base . '/api#subscription">plan</a>.',
    tirreno('utils')->errorCodes->ENRICHMENT_API_ATTR_UNAVAILABLE  => 'Enrichment of this data type is not supported in current subscription.',
    tirreno('utils')->errorCodes->ENRICHMENT_API_IS_NOT_AVAILABLE  => 'API server is currently unavailable. Please try again later.',

    tirreno('utils')->errorCodes->ITEM_REMOVED_FROM_BLACKLIST      => 'Item removed from blacklist.',
    tirreno('utils')->errorCodes->ITEM_REMOVE_FAIL_FROM_BLACKLIST  => 'Item remove from blacklist failed.',

    tirreno('utils')->errorCodes->SUBSCRIPTION_KEY_INVALID_UPDATE  => 'Enrichment key is not valid.',
    tirreno('utils')->errorCodes->TOTALS_INVALID_TYPE              => 'Invalid entity type was passed for totals calculation',
    tirreno('utils')->errorCodes->CRON_JOB_MAY_BE_OFF              => 'A cron job isn\'t running. Please check the cron job configuration.',
];

$baseErrors = (tirreno('storage')->get('EXTRA_DICT_EN_ERRORS') ?? []) + $baseErrors;
foreach ($baseErrors as $key => $value) {
    $errors['error_' . strval($key)] = $value;
}

return $errors;
