<?php

/**
 * tirreno ~ open security analytics
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

namespace Utils;

class Validators {
    private static function getF3(): \Base {
        return \Base::instance();
    }

    // helpers
    private static function getSafeString(string $key, array $params): ?string {
        return isset($params[$key]) && is_string($params[$key]) && $params[$key] ? $params[$key] : null;
    }

    private static function getSafeInt(string $key, array $params): ?int {
        return isset($params[$key]) ? \Utils\Conversion::intVal($params[$key]) : null;
    }

    private static function checkInterval(string $key, array $params, int $start, int $end): bool {
        $value = self::getSafeInt($key, $params);

        return $value !== null && $value >= $start && $value <= $end;
    }

    // basic validators
    private static function validateCsrf(array $params): int|false {
        return \Utils\Access::CSRFTokenValid($params, self::getF3()) ?: false;
    }

    private static function validateEmailPresence(array $params): int|false {
        return !self::getSafeString('email', $params)
            ? \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST
            : false;
    }

    private static function validatePasswordPresence(array $params): int|false {
        return !self::getSafeString('password', $params)
            ? \Utils\ErrorCodes::PASSWORD_DOES_NOT_EXIST
            : false;
    }

    private static function validateTimezone(array $params): int|false {
        return !self::getSafeString('timezone', $params)
            || !array_key_exists($params['timezone'], \Utils\Variables::getAvailableTimezones())
            ? \Utils\ErrorCodes::TIME_ZONE_DOES_NOT_EXIST
            : false;
    }

    private static function validateEmailCorrect(array $params): int|false {
        return !self::getSafeString('email', $params)
            || !\Audit::instance()->email($params['email'], true)
            ? \Utils\ErrorCodes::EMAIL_IS_NOT_CORRECT
            : false;
    }

    private static function validateApiKeyPresence(array $params): int|false {
        return !self::getSafeInt('keyId', $params)
            ? \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST
            : false;
    }

    private static function validateApiKeyOwning(array $params): int|false {
        $keyId = self::getSafeInt('keyId', $params);

        return !$keyId
            || !\Utils\Access::checkCurrentOperatorApiKeyAccess($keyId)
            ? \Utils\ErrorCodes::API_KEY_ID_INVALID
            : false;
    }

    private static function validateEmailNew(array $params): int|false {
        return !self::getSafeString('email', $params)
            || (new \Models\Operator())->getByEmail($params['email'])
            ? \Utils\ErrorCodes::EMAIL_ALREADY_EXIST
            : false;
        /*if ($operatorsModel->loaded()) {*/
    }

    private static function validateNewPasswordPresence(array $params): int|false {
        return !self::getSafeString('new-password', $params)
            ? \Utils\ErrorCodes::NEW_PASSWORD_DOES_NOT_EXIST
            : false;
    }

    private static function validatePasswordLength(array $params): int|false {
        return !self::getSafeString('password', $params)
            || strlen($params['password']) < self::getF3()->get('MIN_PASSWORD_LENGTH')
            ? \Utils\ErrorCodes::PASSWORD_IS_TOO_SHORT
            : false;
    }

    private static function validateNewPasswordLength(array $params): int|false {
        return !self::getSafeString('new-password', $params)
            || strlen($params['new-password']) < self::getF3()->get('MIN_PASSWORD_LENGTH')
            ? \Utils\ErrorCodes::PASSWORD_IS_TOO_SHORT
            : false;
    }

    private static function validateCurrentPasswordPresence(array $params): int|false {
        return !self::getSafeString('current-password', $params)
            ? \Utils\ErrorCodes::CURRENT_PASSWORD_DOES_NOT_EXIST
            : false;
    }

    private static function validateEmailChanged(array $params): int|false {
        return !self::getSafeString('email', $params)
            || strtolower($params['email']) === strtolower(\Utils\Routes::getCurrentRequestOperator()?->email ?? '')
            ? \Utils\ErrorCodes::EMAIL_IS_NOT_NEW
            : false;
    }

    private static function validateEnrichedAttributes(array $params, array $attributes): int|false {
        return !isset($params['enrichedAttributes'])
            || !is_array($params['enrichedAttributes'])
            || array_diff(array_keys($params['enrichedAttributes']), $attributes)
            ? \Utils\ErrorCodes::UNKNOWN_ENRICHMENT_ATTRIBUTES
            : false;
    }

    private static function validateReminderFreq($params): int|false {
        return !isset($params['review-reminder-frequency'])
            || !$params['review-reminder-frequency']
            || (!is_int($params['review-reminder-frequency']) && !is_string($params['review-reminder-frequency']))
            || !in_array($params['review-reminder-frequency'], \Utils\Constants::get('NOTIFICATION_REMINDER_TYPES'))
            ? \Utils\ErrorCodes::INVALID_REMINDER_FREQUENCY
            : false;
    }

    private static function validatePasswordConfirmationPresence(array $params): int|false {
        return !self::getSafeString('password-confirmation', $params)
            ? \Utils\ErrorCodes::PASSWORD_CONFIRMATION_MISSING
            : false;
    }

    private static function validatePasswordCompare(array $params): int|false {
        return !self::getSafeString('password-confirmation', $params)
            || !self::getSafeString('new-password', $params)
            || $params['new-password'] !== $params['password-confirmation']
            ? \Utils\ErrorCodes::PASSWORDS_ARE_NOT_EQUAL
            : false;
    }

    private static function validatePasswordRenewKeyPresence(?array $params): int|false {
        return $params === null
            || !self::getSafeString('renewKey', $params)
            ? \Utils\ErrorCodes::RENEW_KEY_DOES_NOT_EXIST
            : false;
    }

    private static function validateEmailRenewKeyPresence(?array $params): int|false {
        return $params === null
            || !self::getSafeString('renewKey', $params)
            ? \Utils\ErrorCodes::CHANGE_EMAIL_KEY_DOES_NOT_EXIST
            : false;
    }

    private static function validateOperatorIdPresence(array $params): int|false {
        return !self::getSafeInt('operatorId', $params)
            ? \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST
            : false;
    }

    private static function validateRetention($params): int|false {
        return !self::checkInterval('retention-policy', $params, 0, 12)
            ? \Utils\ErrorCodes::RETENTION_POLICY_DOES_NOT_EXIST
            : false;
    }

    private static function validateBlacklistThreshold($params): int|false {
        return (isset($params['blacklist-threshold'])
            && $params['blacklist-threshold'] !== ''
            && !self::checkInterval('blacklist-threshold', $params, 0, 100))
            ? \Utils\ErrorCodes::INVALID_BLACKLIST_THRESHOLD
            : false;
    }

    private static function validateReviewQueueThreshold($params): int|false {
        return !self::checkInterval('review-queue-threshold', $params, 0, 100)
            ? \Utils\ErrorCodes::INVALID_REVIEW_QUEUE_THRESHOLD
            : false;
    }

    private static function validateThresholdsCompare(array $params): int|false {
        $reviewQueueThreshold = self::getSafeInt('review-queue-threshold', $params);
        $blacklistThreshold = self::getSafeInt('blacklist-threshold', $params);

        return $reviewQueueThreshold === null
            || ($blacklistThreshold !== null
            && $reviewQueueThreshold <= $blacklistThreshold)
            ? \Utils\ErrorCodes::INVALID_THRESHOLDS_COMBINATION
            : false;
    }

    private static function validateSearchEnrichment(string $enrichmentKey): int|false {
        return !$enrichmentKey
            || !\Utils\Variables::getEnrichmentApi()
            ? \Utils\ErrorCodes::ENRICHMENT_API_KEY_NOT_EXISTS
            : false;
    }

    private static function validateSearchType(array $params): int|false {
        $type = self::getSafeString('type', $params);
        $types = self::getF3()->get('AdminManualCheck_form_types');

        return !$type
            || !is_array($types)
            || !array_key_exists($type, $types)
            ? \Utils\ErrorCodes::TYPE_DOES_NOT_EXIST
            : false;
    }

    private static function validateSearchValue(array $params): int|false {
        return !self::getSafeString('search', $params)
            ? \Utils\ErrorCodes::SEARCH_QUERY_DOES_NOT_EXIST
            : false;
    }

    private static function validateCurrentPassword(array $params): int|false {
        $operatorId = \Utils\Access::getCurrentOperatorId();
        if (!$operatorId) {
            return \Utils\ErrorCodes::CURRENT_PASSWORD_IS_NOT_CORRECT;
        }

        $model = new \Models\Operator();
        $model->getOperatorById($operatorId);
        if (!$model->verifyPassword($params['current-password'])) {
            return \Utils\ErrorCodes::CURRENT_PASSWORD_IS_NOT_CORRECT;
        }

        return false;
    }

    private static function validateBelongingCoOwner(array $params): int|false {
        $operatorId = self::getSafeInt('operatorId', $params);
        $keyId = \Utils\Access::getCurrentOperatorApiKeyId();

        if (!$operatorId || !$keyId) {
            return \Utils\ErrorCodes::OPERATOR_IS_NOT_A_CO_OWNER;
        }

        $coOwnerModel = new \Models\ApiKeyCoOwner();
        $coOwnerModel->getCoOwnership($operatorId);

        if (!$coOwnerModel->loaded() || $coOwnerModel->api !== $keyId) {
            return \Utils\ErrorCodes::OPERATOR_IS_NOT_A_CO_OWNER;
        }
        return false;
    }

    // settings
    public static function validateCheckUpdates(array $params): int|false {
        return self::validateCsrf($params);
    }

    public static function validateChangeTimeZone(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateTimezone($params);
    }

    public static function validateCloseAccount(array $params): int|false {
        return self::validateCsrf($params);
    }

    public static function validateUpdateNotificationPreferences(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateReminderFreq($params);
    }

    public static function validateChangeEmail(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateEmailPresence($params)
            ?: self::validateEmailCorrect($params)
            ?: self::validateEmailChanged($params)
            ?: self::validateEmailNew($params);
    }

    public static function validateChangePassword(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateCurrentPasswordPresence($params)
            ?: self::validateCurrentPassword($params)
            ?: self::validateNewPasswordPresence($params)
            ?: self::validateNewPasswordLength($params)
            ?: self::validatePasswordConfirmationPresence($params)
            ?: self::validatePasswordCompare($params);
    }

    public static function validateInvitingCoOwner(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateEmailPresence($params)
            ?: self::validateEmailCorrect($params)
            ?: self::validateEmailNew($params);
    }

    public static function validateRemovingCoOwner(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateOperatorIdPresence($params)
            ?: self::validateBelongingCoOwner($params);
    }

    public static function validateRetentionPolicy(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateApiKeyPresence($params)
            ?: self::validateApiKeyOwning($params)
            ?: self::validateRetention($params);
    }

    // api
    public static function validateEnrichAll(array $params): int|false {
        return self::validateCsrf($params);
    }

    public static function validateResetApiKey(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateApiKeyPresence($params)
            ?: self::validateApiKeyOwning($params);
    }

    public static function validateUpdateApiUsage(array $params, array $attributes): int|false {
        return self::validateCsrf($params)
            ?: self::validateApiKeyPresence($params)
            ?: self::validateApiKeyOwning($params)
            ?: self::validateEnrichedAttributes($params, $attributes);
    }

    // rules
    public static function validateThresholdValues(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateApiKeyPresence($params)
            ?: self::validateApiKeyOwning($params)
            ?: self::validateBlacklistThreshold($params)
            ?: self::validateReviewQueueThreshold($params)
            ?: self::validateThresholdsCompare($params);
    }

    public static function validateRefreshRules(array $params): int|false {
        return self::validateCsrf($params);
    }

    // manual-check
    public static function validateSearch(array $params, string $enrichmentKey): int|false {
        return self::validateCsrf($params)
            ?: self::validateSearchEnrichment($enrichmentKey)
            ?: self::validateSearchType($params)
            ?: self::validateSearchValue($params);
    }

    // non-admin pages
    public static function validateForgotPassword(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateEmailPresence($params);
    }

    public static function validateSignup(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateEmailPresence($params)
            ?: self::validatePasswordPresence($params)
            ?: self::validateEmailCorrect($params)
            ?: self::validateEmailNew($params)
            ?: self::validatePasswordLength($params)
            ?: self::validateTimezone($params);
    }

    public static function validatePasswordRecoveringPost(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateNewPasswordPresence($params)
            ?: self::validateNewPasswordLength($params)
            ?: self::validatePasswordConfirmationPresence($params)
            ?: self::validatePasswordCompare($params);
    }

    public static function validateLogin(array $params): int|false {
        return self::validateCsrf($params)
            ?: self::validateEmailPresence($params)
            ?: self::validatePasswordPresence($params);
    }

    public static function validatePasswordRecovering(?array $params): int|false {
        $errorCode = self::validatePasswordRenewKeyPresence($params);
        if ($errorCode) {
            return $errorCode;
        }

        $forgotPasswordModel = new \Models\ForgotPassword();
        $forgotPasswordModel->getUnusedByRenewKey($params['renewKey']);
        if (!$forgotPasswordModel->loaded()) {
            return \Utils\ErrorCodes::RENEW_KEY_IS_NOT_CORRECT;
        }

        $currentTime = time();
        $linkTime = strtotime($forgotPasswordModel->created_at);
        $lifeTime = self::getF3()->get('RENEW_PASSWORD_LINK_TIME');

        if ($currentTime > $linkTime + $lifeTime) {
            return \Utils\ErrorCodes::RENEW_KEY_WAS_EXPIRED;
        }

        return false;
    }

    public static function validateChangeEmailPage(?array $params): int|false {
        $errorCode = self::validateEmailRenewKeyPresence($params);
        if ($errorCode) {
            return $errorCode;
        }

        $changeEmailModel = new \Models\ChangeEmail();
        $changeEmailModel->getByRenewKey($params['renewKey']);
        if (!$changeEmailModel->loaded()) {
            return \Utils\ErrorCodes::CHANGE_EMAIL_KEY_IS_NOT_CORRECT;
        }

        $currentTime = time();
        $linkTime = strtotime($changeEmailModel->created_at);
        $lifeTime = self::getF3()->get('RENEW_PASSWORD_LINK_TIME');

        if ($currentTime > $linkTime + $lifeTime) {
            return \Utils\ErrorCodes::CHANGE_EMAIL_KEY_WAS_EXPIRED;
        }

        return false;
    }
}
