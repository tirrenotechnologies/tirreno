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

namespace Controllers\Admin\Settings;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function proceedPostRequest(array $params): array {
        return match ($params['cmd']) {
            'changeEmail'                   => $this->changeEmail($params),
            'changeTimeZone'                => $this->changeTimeZone($params),
            'changePassword'                => $this->changePassword($params),
            'closeAccount'                  => $this->closeAccount($params),
            'updateNotificationPreferences' => $this->updateNotificationPreferences($params),
            'changeRetentionPolicy'         => $this->changeRetentionPolicy($params),
            'inviteCoOwner'                 => $this->inviteCoOwner($params),
            'removeCoOwner'                 => $this->removeCoOwner($params),
            'checkUpdates'                  => $this->checkUpdates($params),
            default => []
        };
    }

    public function getOperatorApiKeys(int $operatorId): array {
        $model = new \Models\ApiKeys();
        $apiKeys = $model->getKeys($operatorId);

        $isOwner = true;
        if (!$apiKeys) {
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            if ($coOwnerModel->loaded()) {
                $isOwner = false;
                $apiKeys[] = $model->getKeyById($coOwnerModel->api);
            }
        }

        return [$isOwner, $apiKeys];
    }

    public function getSharedApiKeyOperators(int $operatorId): array {
        $model = new \Models\ApiKeyCoOwner();

        return $model->getSharedApiKeyOperators($operatorId);
    }

    public function changePassword(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateChangePassword($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $model = new \Models\Operator();
            $model->updatePassword($params);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_changePassword_success_message');
        }

        return $pageParams;
    }

    public function changeEmail(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateChangeEmail($params);

        if ($errorCode) {
            $pageParams['EMAIL_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentOperator = $this->f3->get('CURRENT_USER');
            $operatorId = $currentOperator->id;
            $email = $params['email'];

            // Create change email record
            $changeEmailModel = new \Models\ChangeEmail();
            $changeEmailModel->add($operatorId, $email);

            // Send forgot password email
            $this->sendChangeEmailEmail($currentOperator, $changeEmailModel);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_changeEmail_success_message');
        }

        return $pageParams;
    }

    public function changeTimeZone(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateChangeTimeZone($params);

        if ($errorCode) {
            $pageParams['TIME_ZONE_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $model = new \Models\Operator();
            $model->updateTimeZone($params);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminTimeZone_changeTimeZone_success_message');
        }

        return $pageParams;
    }

    public function closeAccount(array $data): array {
        $pageParams = [];
        $errorCode = $this->validateCloseAccount($data);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = $data['id'];
            $apiKey = $this->getCurrentOperatorApiKeyId();

            $model = new \Models\Operator();
            $model->closeAccount($operatorId);
            $model->removeData($operatorId);

            $this->f3->clear('SESSION');
            session_commit();

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_closeAccount_success_message');
        }

        return $pageParams;
    }

    public function checkUpdates(array $data): array {
        $pageParams = [];
        $errorCode = $this->validateCheckUpdates($data);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentVersion = \Utils\VersionControl::versionString();

            $result = \Web::instance()->request(
                url: \Utils\Variables::getEnrichtmentApi() . '/version',
                options: [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: ' . $this->f3->get('USER_AGENT'),
                    ],
                ],
            );
            $matches = [];
            preg_match('/^HTTP\/(\d+)(?:\.\d)? (\d{3})/', $result['headers'][0], $matches);
            $jsonResponse = json_decode($result['body'], true);
            $statusCode = (int) ($matches[2] ?? 0);
            $errorMessage = $result['error'];

            if (strlen($errorMessage) > 0 || $statusCode !== 200 || !is_array($jsonResponse)) {
                $pageParams['ERROR_CODE'] = \Utils\ErrorCodes::ENRICHMENT_API_IS_NOT_AVAILABLE;
            } else {
                if (version_compare($currentVersion, $jsonResponse['version'], '<')) {
                    $pageParams['SUCCESS_MESSAGE'] = sprintf('An update is available. Released date: %s.', $jsonResponse['release_date']);
                } else {
                    $pageParams['SUCCESS_MESSAGE'] = 'Current version is up to date.';
                }
            }
        }

        return $pageParams;
    }

    public function validateCheckUpdates(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        return false;
    }

    public function updateNotificationPreferences(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateUpdateNotificationPreferences($params);

        if ($errorCode) {
            $pageParams['PROFILE_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $unreviewedItemsReminderFrequency = $params['review-reminder-frequency'];
            $operatorId = $params['id'];

            $operatorModel = new \Models\Operator();
            $operatorModel->updateNotificationPreferences(
                \Type\UnreviewedItemsReminderFrequencyType::from($unreviewedItemsReminderFrequency),
                $operatorId,
            );

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_notificationPreferences_success_message');
        }

        return $pageParams;
    }

    public function changeRetentionPolicy(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateRetentionPolicy($params);

        if ($errorCode) {
            $pageParams['RETENTION_POLICY_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;
            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);
            $model->updateRetentionPolicy((int) ($params['retention-policy'] ?? 0));
            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminRetentionPolicy_changeTimeZone_success_message');
        }

        return $pageParams;
    }

    public function validateRetentionPolicy(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;
        if (!$keyId) {
            return \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST;
        }

        if ($keyId !== $this->getCurrentOperatorApiKeyId()) {
            return \Utils\ErrorCodes::API_KEY_WAS_CREATED_FOR_ANOTHER_USER;
        }

        $retentionPolicy = (int) ($params['retention-policy'] ?? 0);
        if ($retentionPolicy < 0 || $retentionPolicy > 12) {
            return \Utils\ErrorCodes::RETENTION_POLICY_DOES_NOT_EXIST;
        }

        return false;
    }

    public function inviteCoOwner(array $params): array {
        $errorCode = $this->validateInvitingCoOwner($params);
        $pageParams = [];

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentOperator = $this->f3->get('CURRENT_USER');
            $operatorId = $currentOperator->id;

            $apiKeyModel = new \Models\ApiKeys();
            $key = $apiKeyModel->getKey($operatorId);

            $params['timezone'] = 'UTC';
            $operator = new \Models\Operator();
            $operator->add($params);

            $passwordReset = new \Models\ForgotPassword();
            $passwordReset->add($operator->id);

            $this->makeOperatorCoOwner($operator, $key);
            $this->sendInvitationEmail($currentOperator, $operator, $passwordReset);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_add_co_owner_success_message');
        }

        return $pageParams;
    }

    public function removeCoOwner(array $params): array {
        $errorCode = $this->validateRemovingCoOwner($params);
        $pageParams = [];

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = isset($params['operatorId']) ? (int) $params['operatorId'] : null;
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);
            $coOwnerModel->deleteCoOwnership();

            $operatorModel = new \Models\Operator();
            $operatorModel->getOperatorById($operatorId);
            $operatorModel->deleteAccount();

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_remove_co_owner_success_message');
        }

        return $pageParams;
    }

    private function makeOperatorCoOwner(\Models\Operator $operator, \Models\ApiKeys $key): void {
        $model = new \Models\ApiKeyCoOwner();
        $model->create($operator->id, $key->id);
    }

    private function sendInvitationEmail(\Models\Operator $inviter, \Models\Operator $operator, \Models\ForgotPassword $forgotPassword): void {
        $site = \Utils\Variables::getSite();

        $inviterDisplayName = $inviter->email;
        if ($inviter->firstname && $inviter->lastname) {
            $inviterDisplayName = sprintf('%s %s (%s)', $inviter->firstname, $inviter->lastname, $inviterDisplayName);
        }

        $toName = null;
        $toAddress = $operator->email;
        $renewKey = $forgotPassword->renew_key;

        $subject = $this->f3->get('AdminApi_invitation_email_subject');
        $message = $this->f3->get('AdminApi_invitation_email_body');

        $renewUrl = sprintf('%s/password-recovering/%s', $site, $renewKey);
        $message = sprintf($message, $inviterDisplayName, $renewUrl);

        \Utils\Mailer::send($toName, $toAddress, $subject, $message);
    }

    private function validateInvitingCoOwner(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $email = $params['email'] ?? null;
        if (!$email) {
            return \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST;
        }

        $audit = \Audit::instance();
        if (!$audit->email($email, true)) {
            return \Utils\ErrorCodes::EMAIL_IS_NOT_CORRECT;
        }

        $operatorsModel = new \Models\Operator();
        $operatorsModel->getByEmail($email);
        if ($operatorsModel->loaded()) {
            return \Utils\ErrorCodes::EMAIL_ALREADY_EXIST;
        }

        return false;
    }

    public function validateRemovingCoOwner(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $operatorId = isset($params['operatorId']) ? (int) $params['operatorId'] : null;
        if (!$operatorId) {
            return \Utils\ErrorCodes::OPERATOR_ID_DOES_NOT_EXIST;
        }

        $apiKey = $this->getCurrentOperatorApiKeyId();
        $coOwnerModel = new \Models\ApiKeyCoOwner();
        $coOwnerModel->getCoOwnership($operatorId);

        if (!$coOwnerModel->loaded() || $coOwnerModel->api !== $apiKey) {
            return \Utils\ErrorCodes::OPERATOR_IS_NOT_A_CO_OWNER;
        }

        return false;
    }

    public function validateChangePassword(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $currentPassword = $params['currentPassword'];
        if (!$currentPassword) {
            return \Utils\ErrorCodes::CURRENT_PASSWORD_DOES_NOT_EXIST;
        }

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;

        $model = new \Models\Operator();
        $model->getOperatorById($operatorId);
        if (!$model->verifyPassword($currentPassword)) {
            return \Utils\ErrorCodes::CURRENT_PASSWORD_IS_NOT_CORRECT;
        }

        $newPassword = $params['new-password'];
        if (!$newPassword) {
            return \Utils\ErrorCodes::NEW_PASSWORD_DOES_NOT_EXIST;
        }

        $newPasswordLegth = strlen($newPassword);
        $minPasswordLegth = $this->f3->get('MIN_PASSWORD_LENGTH');
        if ($newPasswordLegth < $minPasswordLegth) {
            return \Utils\ErrorCodes::PASSWORD_IS_TO_SHORT;
        }

        $passwordConfirmation = $params['password-confirmation'];
        if (!$passwordConfirmation) {
            return \Utils\ErrorCodes::PASSWORD_CONFIRMATION_DOES_NOT_EXIST;
        }

        if ($newPassword !== $passwordConfirmation) {
            return \Utils\ErrorCodes::PASSWORDS_ARE_NOT_EQUAL;
        }

        return false;
    }

    public function validateChangeEmail(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $email = $params['email'] ?? null;
        if (!$email) {
            return \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST;
        }

        $audit = \Audit::instance();
        if (!$audit->email($email, true)) {
            return \Utils\ErrorCodes::EMAIL_IS_NOT_CORRECT;
        }

        $currentOperator = $this->f3->get('CURRENT_USER');
        if (\strtolower($currentOperator->email) === \strtolower($email)) {
            return \Utils\ErrorCodes::EMAIL_IS_NOT_NEW;
        }

        $operatorModel = new \Models\Operator();
        $operatorModel->getByEmail($email);
        if ($operatorModel->loaded() && $currentOperator->id !== $operatorModel->id) {
            return \Utils\ErrorCodes::EMAIL_ALREADY_EXIST;
        }

        return false;
    }

    public function validateChangeTimeZone(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $timezone = $params['timezone'] ?? null;
        $timezones = $this->f3->get('timezones');
        if (!$timezone || !array_key_exists($timezone, $timezones)) {
            return \Utils\ErrorCodes::TIME_ZONE_DOES_NOT_EXIST;
        }

        return false;
    }

    public function validateCloseAccount(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        return false;
    }

    public function validateUpdateNotificationPreferences(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $unreviewedItemsReminderFrequency = $params['review-reminder-frequency'] ?? null;
        if (!$unreviewedItemsReminderFrequency || !\Type\UnreviewedItemsReminderFrequencyType::tryFrom($unreviewedItemsReminderFrequency)) {
            return \Utils\ErrorCodes::UNREVIEWED_ITEMS_REMINDER_FREQUENCY_DOES_NOT_EXIST;
        }

        return false;
    }

    private function sendChangeEmailEmail(\Models\Operator $currentOperator, \Models\ChangeEmail $changeEmailModel): void {
        $url = \Utils\Variables::getSiteWithProtocol();

        $toName = $currentOperator->firstname;
        $toAddress = $changeEmailModel->email;
        $renewKey = $changeEmailModel->renew_key;

        $subject = $this->f3->get('ChangeEmail_renew_email_subject');
        $message = $this->f3->get('ChangeEmail_renew_email_body');

        $renewUrl = sprintf('%s/change-email/%s', $url, $renewKey);
        $message = sprintf($message, $renewUrl);

        \Utils\Mailer::send($toName, $toAddress, $subject, $message);
    }
}
