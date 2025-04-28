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

if (!function_exists('addExerciseError')) {
    function addExerciseError($errors, $code, $message) {
        $key = sprintf('error_%s', $code);
        $errors[$key] = $message;

        return $errors;
    }
}

$errors = [
    'error_email_subject' => 'Error %s occurred',
    'error_email_body_template' => (
        '<p>Error occurred at: %s</p>
        <p>Message: </p>%s
        <p>Trace: </p>%s
        '
    ),
];

$errors = addExerciseError($errors, 404, 'Page not found');
$errors = addExerciseError($errors, 500, 'This function does not work right now');
$errors = addExerciseError($errors, \Utils\ErrorCodes::CSRF_ATTACK_DETECTED, 'We can\'t proceed with this request. Please reload the page and try again');
$errors = addExerciseError($errors, \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST, 'Email does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::EMAIL_IS_NOT_CORRECT, 'Email is incorrect');
$errors = addExerciseError($errors, \Utils\ErrorCodes::EMAIL_ALREADY_EXIST, 'Email already exists');
$errors = addExerciseError($errors, \Utils\ErrorCodes::PASSWORD_DOES_NOT_EXIST, 'Password does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::PASSWORD_IS_TO_SHORT, 'Minimum password length is 8 characters');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ACCOUNT_CREATED, 'Thanks for your registration. Please <a href="/login">login</a> with your new credentials.');

$errors = addExerciseError($errors, \Utils\ErrorCodes::ACTIVATION_KEY_DOES_NOT_EXIST, 'Activation key does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ACTIVATION_KEY_IS_NOT_CORRECT, 'Activation key is incorrect');
$errors = addExerciseError($errors, \Utils\ErrorCodes::EMAIL_OR_PASSWORD_IS_NOT_CORRECT, 'Error: Permission denied.');

$errors = addExerciseError($errors, \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST, 'API key does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::API_KEY_WAS_CREATED_FOR_ANOTHER_USER, 'Incorrect Tracking ID');
$errors = addExerciseError($errors, \Utils\ErrorCodes::OPERATOR_ID_DOES_NOT_EXIST, 'Operator ID does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::OPERATOR_IS_NOT_A_CO_OWNER, 'Operator is not a co-owner of this Tracking ID');
$errors = addExerciseError($errors, \Utils\ErrorCodes::UNKNOWN_ENRICHMENT_ATTRIBUTES, 'Unknown event attributes for data enrichment');
$errors = addExerciseError($errors, \Utils\ErrorCodes::INVALID_API_RESPONSE, 'Unexpected API response');

$errors = addExerciseError($errors, \Utils\ErrorCodes::FIRST_NAME_DOES_NOT_EXIST, 'First name is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::LAST_NAME_DOES_NOT_EXIST, 'Last name is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::COUNTRY_DOES_NOT_EXIST, 'Country is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::STREET_DOES_NOT_EXIST, 'Street address is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::CITY_DOES_NOT_EXIST, 'City is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::STATE_DOES_NOT_EXIST, 'State is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ZIP_DOES_NOT_EXIST, 'ZIP is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::TIME_ZONE_DOES_NOT_EXIST, 'Time zone is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::RETENTION_POLICY_DOES_NOT_EXIST, 'Retention policy is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::UNREVIEWED_ITEMS_REMINDER_FREQUENCY_DOES_NOT_EXIST, 'Unreviewed items reminder frequency is a mandatory field');

$errors = addExerciseError($errors, \Utils\ErrorCodes::CURRENT_PASSWORD_DOES_NOT_EXIST, 'Current password is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::CURRENT_PASSWORD_IS_NOT_CORRECT, 'Current password is incorrect');
$errors = addExerciseError($errors, \Utils\ErrorCodes::NEW_PASSWORD_DOES_NOT_EXIST, 'New password is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::PASSWORD_CONFIRMATION_DOES_NOT_EXIST, 'Password confirmation is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::PASSWORDS_ARE_NOT_EQUAL, 'New password and password confirmation do not match');
$errors = addExerciseError($errors, \Utils\ErrorCodes::EMAIL_IS_NOT_NEW, 'The new email address is the same as the current one');

$errors = addExerciseError($errors, \Utils\ErrorCodes::RENEW_KEY_CREATED, 'We sent you an email with further instructions on how to reset your password');
$errors = addExerciseError($errors, \Utils\ErrorCodes::RENEW_KEY_IS_NOT_CORRECT, 'Renew key is incorrect  ¯\_ (ツ)_/¯');
$errors = addExerciseError($errors, \Utils\ErrorCodes::RENEW_KEY_DOES_NOT_EXIST, 'Renew key does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::RENEW_KEY_WAS_EXPIRED, 'Renew key has expired');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ACCOUNT_ACTIVATED, 'Your password has been successfully changed. Please <a href="/login">login</a> with your new credentials and continue using the system.');

$errors = addExerciseError($errors, \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_YET, 'No events from your application have been received yet');
$errors = addExerciseError($errors, \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_LAST_24_HOURS, 'There are no events from your application for more than 24 hours');

$errors = addExerciseError($errors, \Utils\ErrorCodes::OPERATOR_DOES_NOT_HAVE_ACCESS_TO_ACCOUNT, 'Operator does not have access to this account');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_HAS_BEEN_SUCCESSFULLY_ADDED_TO_WATCH_LIST, 'User has been successfully added to the watchlist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_HAS_BEEN_SUCCESSFULLY_REMOVED_FROM_WATCH_LIST, 'User has been successfully removed from the watchlist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_FRAUD_FLAG_HAS_BEEN_SET, 'User has been successfully marked as fraud');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_FRAUD_FLAG_HAS_BEEN_UNSET, 'User has been successfully marked as not fraud');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_REVIEWED_FLAG_HAS_BEEN_SET, 'User has been successfully marked as reviewed');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_REVIEWED_FLAG_HAS_BEEN_UNSET, 'User has been successfully marked as not reviewed');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_DELETION_FAILED, 'User deletion was unsuccessful.');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_BLACKLISTING_FAILED, 'User blacklisting was unsuccessful.');
$errors = addExerciseError($errors, \Utils\ErrorCodes::USER_BLACKLISTING_QUEUED, 'This user and all associated IPs are currently queued for blacklisting.');

$errors = addExerciseError($errors, \Utils\ErrorCodes::CHANGE_EMAIL_KEY_DOES_NOT_EXIST, 'Change email key does not exist');
$errors = addExerciseError($errors, \Utils\ErrorCodes::CHANGE_EMAIL_KEY_IS_NOT_CORRECT, 'Change email key is incorrect');
$errors = addExerciseError($errors, \Utils\ErrorCodes::CHANGE_EMAIL_KEY_WAS_EXPIRED, 'Change email key has expired');
$errors = addExerciseError($errors, \Utils\ErrorCodes::EMAIL_CHANGED, 'Your email has been successfully changed. Please <a href="/login">login</a> with your new credentials and continue using the system.');
$errors = addExerciseError($errors, \Utils\ErrorCodes::RULES_HAS_BEEN_SUCCESSFULLY_UPDATED, 'Rules have been successfully updated');
$errors = addExerciseError($errors, \Utils\ErrorCodes::BLACKLIST_THRESHOLD_DOES_NOT_EXIST, 'Blacklist threshold is mandatory fields');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REVIEW_QUEUE_THRESHOLD_DOES_NOT_EXIST, 'Review queue threshold is mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::BLACKLIST_THRESHOLD_EXCEEDS_REVIEW_QUEUE_THRESHOLD, 'Blacklist threshold must not exceed review queue threshold.');

$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_KEY_DOES_NOT_EXIST, 'API key could not be found in the headers');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_KEY_IS_NOT_CORRECT, 'API key is incorrect');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_NOT_AUTHORIZED, 'Not authorized to perform this action');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_MISSING_PARAMETER, 'Missing required parameter');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_VALIDATION_ERROR, 'Validation error');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_USER_ALREADY_SCHEDULED_FOR_DELETION, 'User already scheduled for deletion');
$errors = addExerciseError($errors, \Utils\ErrorCodes::REST_API_USER_SUCCESSFULLY_ADDED_FOR_DELETION, 'User successfully added for deletion');

$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_KEY_DOES_NOT_EXIST, 'Enrichment API key is not set');
$errors = addExerciseError($errors, \Utils\ErrorCodes::TYPE_DOES_NOT_EXIST, 'Type is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::SEARCH_QUERY_DOES_NOT_EXIST, 'Search query is a mandatory field');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_UNKNOWN_ERROR, 'Unknown error occurred while processing your request');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_BOGON_IP, 'IP is bogon');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_IP_NOT_FOUND, 'IP not found');
$errors = addExerciseError($errors, \Utils\ErrorCodes::RISK_SCORE_UPDATE_UNKNOWN_ERROR, 'Unknown error occurred while processing your request');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_KEY_OVERUSE, 'You\'ve used up your Enrichment API quota. Please update your <a href="/api#subscription">plan</a>.');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_ATTRIBUTE_IS_UNAVAILABLE, 'Enrichment of this data type is not supported in current subscription.');
$errors = addExerciseError($errors, \Utils\ErrorCodes::ENRICHMENT_API_IS_NOT_AVAILABLE, 'API server is currently unavailable. Please try again later.');

$errors = addExerciseError($errors, \Utils\ErrorCodes::SUBSCRIPTION_KEY_INVALID_UPDATE, 'Subscription key is not valid, canceling update');

$errors = addExerciseError($errors, \Utils\ErrorCodes::TOTALS_INVALID_TYPE, 'Invalid entity type was passed for totals calculation');

$errors = addExerciseError($errors, \Utils\ErrorCodes::CRON_JOB_MAY_BE_OFF, 'A cron job isn\'t running. Please check the cron job configuration.');

$extraErrors = \Base::instance()->get('EXTRA_DICT_EN_ERRORS') ?? [];
foreach ($extraErrors as $errorCode => $desc) {
    $errors = addExerciseError($errors, $errorCode, $desc);
}

return $errors;
