<?php

/**
 * tirreno ~ open security analytics
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

$base = \Base::instance()->get('BASE');
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
    \Utils\ErrorCodes::CSRF_ATTACK_DETECTED             => 'We can\'t proceed with this request. Please reload the page and try again',
    \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST             => 'Email does not exist',
    \Utils\ErrorCodes::EMAIL_IS_NOT_CORRECT             => 'Email is incorrect',
    \Utils\ErrorCodes::EMAIL_ALREADY_EXIST              => 'Email already exists',
    \Utils\ErrorCodes::PASSWORD_DOES_NOT_EXIST          => 'Password does not exist',
    \Utils\ErrorCodes::PASSWORD_IS_TOO_SHORT            => 'Minimum password length is 8 characters',
    \Utils\ErrorCodes::ACCOUNT_CREATED                  => 'Thanks for your registration. Please <a href="' . $base . '/login">login</a> with your new credentials.',
    \Utils\ErrorCodes::INSTALL_DIR_EXISTS               => 'Please delete /install folder before continue',

    \Utils\ErrorCodes::ACTIVATION_KEY_DOES_NOT_EXIST    => 'Activation key does not exist',
    \Utils\ErrorCodes::ACTIVATION_KEY_IS_NOT_CORRECT    => 'Activation key is incorrect',
    \Utils\ErrorCodes::EMAIL_OR_PASSWORD_IS_NOT_CORRECT => 'Error: Permission denied.',

    \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST          => 'API key does not exist',
    \Utils\ErrorCodes::API_KEY_ID_INVALID               => 'Incorrect Tracking ID',
    \Utils\ErrorCodes::OPERATOR_ID_DOES_NOT_EXIST       => 'Operator ID does not exist',
    \Utils\ErrorCodes::OPERATOR_IS_NOT_A_CO_OWNER       => 'Operator is not a co-owner of this Tracking ID',
    \Utils\ErrorCodes::UNKNOWN_ENRICHMENT_ATTRIBUTES    => 'Unknown event attributes for data enrichment',
    \Utils\ErrorCodes::INVALID_API_RESPONSE             => 'Unexpected API response',

    \Utils\ErrorCodes::FIRST_NAME_DOES_NOT_EXIST        => 'First name is a mandatory field',
    \Utils\ErrorCodes::LAST_NAME_DOES_NOT_EXIST         => 'Last name is a mandatory field',
    \Utils\ErrorCodes::COUNTRY_DOES_NOT_EXIST           => 'Country is a mandatory field',
    \Utils\ErrorCodes::STREET_DOES_NOT_EXIST            => 'Street address is a mandatory field',
    \Utils\ErrorCodes::CITY_DOES_NOT_EXIST              => 'City is a mandatory field',
    \Utils\ErrorCodes::STATE_DOES_NOT_EXIST             => 'State is a mandatory field',
    \Utils\ErrorCodes::ZIP_DOES_NOT_EXIST               => 'ZIP is a mandatory field',
    \Utils\ErrorCodes::TIME_ZONE_DOES_NOT_EXIST         => 'Time zone is a mandatory field',
    \Utils\ErrorCodes::RETENTION_POLICY_DOES_NOT_EXIST  => 'Retention policy is a mandatory field',
    \Utils\ErrorCodes::INVALID_REMINDER_FREQUENCY       => 'Unreviewed items reminder frequency is a mandatory field',

    \Utils\ErrorCodes::CURRENT_PASSWORD_DOES_NOT_EXIST  => 'Current password is a mandatory field',
    \Utils\ErrorCodes::CURRENT_PASSWORD_IS_NOT_CORRECT  => 'Current password is incorrect',
    \Utils\ErrorCodes::NEW_PASSWORD_DOES_NOT_EXIST      => 'New password is a mandatory field',
    \Utils\ErrorCodes::PASSWORD_CONFIRMATION_MISSING    => 'Password confirmation is a mandatory field',
    \Utils\ErrorCodes::PASSWORDS_ARE_NOT_EQUAL          => 'New password and password confirmation do not match',
    \Utils\ErrorCodes::EMAIL_IS_NOT_NEW                 => 'The new email address is the same as the current one',

    \Utils\ErrorCodes::RENEW_KEY_CREATED                => 'We sent you an email with further instructions on how to reset your password',
    \Utils\ErrorCodes::RENEW_KEY_IS_NOT_CORRECT         => 'Renew key is incorrect  ¯\_ (ツ)_/¯',
    \Utils\ErrorCodes::RENEW_KEY_DOES_NOT_EXIST         => 'Renew key does not exist',
    \Utils\ErrorCodes::RENEW_KEY_WAS_EXPIRED            => 'Renew key has expired',
    \Utils\ErrorCodes::ACCOUNT_ACTIVATED                => 'Your password has been successfully changed. Please <a href="' . $base . '/login">login</a> with your new credentials and continue using the system.',

    \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_YET          => 'No events from your application have been received yet',
    \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_LAST_DAY     => 'There are no events from your application for more than 24 hours',

    \Utils\ErrorCodes::USER_ADDED_TO_WATCHLIST          => 'User has been successfully added to the watchlist',
    \Utils\ErrorCodes::USER_REMOVED_FROM_WATCHLIST      => 'User has been successfully removed from the watchlist',
    \Utils\ErrorCodes::USER_FRAUD_FLAG_SET              => 'User has been successfully marked as fraud',
    \Utils\ErrorCodes::USER_FRAUD_FLAG_UNSET            => 'User has been successfully marked as not fraud',
    \Utils\ErrorCodes::USER_REVIEWED_FLAG_SET           => 'User has been successfully marked as reviewed',
    \Utils\ErrorCodes::USER_REVIEWED_FLAG_UNSET         => 'User has been successfully marked as not reviewed',
    \Utils\ErrorCodes::USER_DELETION_FAILED             => 'User deletion was unsuccessful.',
    \Utils\ErrorCodes::USER_BLACKLISTING_FAILED         => 'User blacklisting was unsuccessful.',
    \Utils\ErrorCodes::USER_BLACKLISTING_QUEUED         => 'This user and all associated IPs are currently queued for blacklisting.',

    \Utils\ErrorCodes::CHANGE_EMAIL_KEY_DOES_NOT_EXIST    => 'Change email key does not exist',
    \Utils\ErrorCodes::CHANGE_EMAIL_KEY_IS_NOT_CORRECT  => 'Change email key is incorrect',
    \Utils\ErrorCodes::CHANGE_EMAIL_KEY_WAS_EXPIRED     => 'Change email key has expired',
    \Utils\ErrorCodes::EMAIL_CHANGED                    => 'Your email has been successfully changed. Please <a href="' . $base . '/login">login</a> with your new credentials and continue using the system.',
    \Utils\ErrorCodes::RULES_SUCCESSFULLY_UPDATED       => 'Rules have been successfully updated',
    \Utils\ErrorCodes::INVALID_BLACKLIST_THRESHOLD      => 'Blacklist threshold is a mandatory field.',
    \Utils\ErrorCodes::INVALID_REVIEW_QUEUE_THRESHOLD   => 'Review queue threshold is a mandatory field.',
    \Utils\ErrorCodes::INVALID_THRESHOLDS_COMBINATION   => 'Blacklist threshold must not exceed review queue threshold.',

    \Utils\ErrorCodes::REST_API_KEY_DOES_NOT_EXIST      => 'API key could not be found in the headers',
    \Utils\ErrorCodes::REST_API_KEY_IS_NOT_CORRECT      => 'API key is incorrect',
    \Utils\ErrorCodes::REST_API_NOT_AUTHORIZED          => 'Not authorized to perform this action',
    \Utils\ErrorCodes::REST_API_MISSING_PARAMETER       => 'Missing required parameter',
    \Utils\ErrorCodes::REST_API_VALIDATION_ERROR        => 'Validation error',
    \Utils\ErrorCodes::REST_API_USER_ALREADY_DELETING   => 'User already scheduled for deletion',
    \Utils\ErrorCodes::REST_API_USER_ADDED_FOR_DELETION => 'User added to deletion queue',

    \Utils\ErrorCodes::ENRICHMENT_API_KEY_NOT_EXISTS    => 'Enrichment API key is not set',
    \Utils\ErrorCodes::TYPE_DOES_NOT_EXIST              => 'Type is a mandatory field',
    \Utils\ErrorCodes::SEARCH_QUERY_DOES_NOT_EXIST      => 'Search query is a mandatory field',
    \Utils\ErrorCodes::ENRICHMENT_API_UNKNOWN_ERROR     => 'Unknown error occurred while processing your request',
    \Utils\ErrorCodes::ENRICHMENT_API_BOGON_IP          => 'IP is bogon',
    \Utils\ErrorCodes::ENRICHMENT_API_IP_NOT_FOUND      => 'IP not found',
    \Utils\ErrorCodes::RISK_SCORE_UPDATE_UNKNOWN_ERROR  => 'Unknown error occurred while processing your request',
    \Utils\ErrorCodes::ENRICHMENT_API_KEY_OVERUSE       => 'You\'ve used up your Enrichment API quota. Please update your <a href="' . $base . '/api#subscription">plan</a>.',
    \Utils\ErrorCodes::ENRICHMENT_API_ATTR_UNAVAILABLE  => 'Enrichment of this data type is not supported in current subscription.',
    \Utils\ErrorCodes::ENRICHMENT_API_IS_NOT_AVAILABLE  => 'API server is currently unavailable. Please try again later.',

    \Utils\ErrorCodes::ITEM_REMOVED_FROM_BLACKLIST      => 'Item removed from blacklist.',
    \Utils\ErrorCodes::ITEM_REMOVE_FAIL_FROM_BLACKLIST  => 'Item remove from blacklist failed.',

    \Utils\ErrorCodes::SUBSCRIPTION_KEY_INVALID_UPDATE  => 'Enrichment key is not valid.',
    \Utils\ErrorCodes::TOTALS_INVALID_TYPE              => 'Invalid entity type was passed for totals calculation',
    \Utils\ErrorCodes::CRON_JOB_MAY_BE_OFF              => 'A cron job isn\'t running. Please check the cron job configuration.',
];

$baseErrors = (\Base::instance()->get('EXTRA_DICT_EN_ERRORS') ?? []) + $baseErrors;
foreach ($baseErrors as $key => $value) {
    $errors['error_' . strval($key)] = $value;
}

return $errors;
