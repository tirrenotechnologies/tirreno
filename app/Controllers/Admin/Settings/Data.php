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

namespace Controllers\Admin\Settings;

class Data extends \Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return match (\Utils\Conversion::getStringRequestParam('cmd')) {
            'changeEmail'                   => $this->changeEmail(),
            'changeTimeZone'                => $this->changeTimeZone(),
            'changePassword'                => $this->changePassword(),
            'closeAccount'                  => $this->closeAccount(),
            'updateNotificationPreferences' => $this->updateNotificationPreferences(),
            'changeRetentionPolicy'         => $this->changeRetentionPolicy(),
            'inviteCoOwner'                 => $this->inviteCoOwner(),
            'removeCoOwner'                 => $this->removeCoOwner(),
            'checkUpdates'                  => $this->checkUpdates(),
            default => []
        };
    }

    public function getSharedApiKeyOperators(int $operatorId): array {
        $model = new \Models\ApiKeyCoOwner();

        return $model->getSharedApiKeyOperators($operatorId);
    }

    protected function changePassword(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'current-password', 'new-password', 'password-confirmation']);
        $errorCode = \Utils\Validators::validateChangePassword($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $password = \Utils\Conversion::getStringRequestParam('new-password');
            $operatorId = \Utils\Routes::getCurrentRequestOperator()->id;

            $model = new \Models\Operator();
            $model->updatePassword($password, $operatorId);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_changePassword_success_message');
        }

        return $pageParams;
    }

    protected function changeEmail(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'email']);
        $errorCode = \Utils\Validators::validateChangeEmail($params);

        if ($errorCode) {
            $pageParams['EMAIL_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentOperator = \Utils\Routes::getCurrentRequestOperator();
            $email = \Utils\Conversion::getStringRequestParam('email');
            $operatorId = $currentOperator->id;

            // Create change email record
            $changeEmailModel = new \Models\ChangeEmail();
            $changeEmailModel->add($operatorId, $email);

            // Send forgot password email
            $this->sendChangeEmailEmail($currentOperator, $changeEmailModel);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_changeEmail_success_message');
        }

        return $pageParams;
    }

    protected function changeTimeZone(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'timezone']);
        $errorCode = \Utils\Validators::validateChangeTimeZone($params);

        if ($errorCode) {
            $pageParams['TIME_ZONE_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $timezone = \Utils\Conversion::getStringRequestParam('timezone');
            $operatorId = \Utils\Routes::getCurrentRequestOperator()->id;

            $model = new \Models\Operator();
            $model->updateTimeZone($timezone, $operatorId);

            // update operator in f3 hive for clock
            \Utils\Routes::setCurrentRequestOperator();

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminTimeZone_changeTimeZone_success_message');
        }

        return $pageParams;
    }

    protected function closeAccount(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token']);
        $errorCode = \Utils\Validators::validateCloseAccount($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentOperator = \Utils\Routes::getCurrentRequestOperator();
            $currentOperator->closeAccount();
            $currentOperator->removeData();

            $this->f3->clear('SESSION');
            session_commit();

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_closeAccount_success_message');
        }

        return $pageParams;
    }

    protected function checkUpdates(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token']);
        $errorCode = \Utils\Validators::validateCheckUpdates($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentVersion = \Utils\VersionControl::versionString();

            $response = \Utils\Network::sendApiRequest(null, '/version', 'GET', null);
            $code = $response['code'];
            $result = $response['data'];

            $jsonResponse = is_array($result) ? $result : [];
            $statusCode = $code ?? 0;

            $errorMessage = $response['error'] ?? '';

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

    protected function updateNotificationPreferences(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'review-reminder-frequency']);
        $errorCode = \Utils\Validators::validateUpdateNotificationPreferences($params);

        if ($errorCode) {
            $pageParams['PROFILE_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $reminder = \Utils\Conversion::getStringRequestParam('review-reminder-frequency');
            $currentOperator = \Utils\Routes::getCurrentRequestOperator();

            $currentOperator->updateNotificationPreferences($reminder);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminSettings_notificationPreferences_success_message');
        }

        return $pageParams;
    }

    protected function changeRetentionPolicy(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'keyId', 'retention-policy']);
        $errorCode = \Utils\Validators::validateRetentionPolicy($params);

        if ($errorCode) {
            $pageParams['RETENTION_POLICY_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = \Utils\Conversion::getIntRequestParam('keyId');
            $retentionPolicy = \Utils\Conversion::getIntRequestParam('retention-policy');

            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);
            $model->updateRetentionPolicy($retentionPolicy);
            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminRetentionPolicy_changeTimeZone_success_message');
        }

        return $pageParams;
    }

    protected function inviteCoOwner(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'email']);
        $errorCode = \Utils\Validators::validateInvitingCoOwner($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentOperator = \Utils\Routes::getCurrentRequestOperator();
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

    protected function removeCoOwner(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'operatorId']);
        $errorCode = \Utils\Validators::validateRemovingCoOwner($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = \Utils\Conversion::getIntRequestParam('operatorId');

            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            $apiKeyObj = \Utils\ApiKeys::getCurrentOperatorApiKeyObject();

            if ($apiKeyObj->id === $coOwnerModel->api && \Utils\Routes::getCurrentRequestOperator()->id === $apiKeyObj->creator) {
                $coOwnerModel->deleteCoOwnership();

                $operatorModel = new \Models\Operator();
                $operatorModel->getOperatorById($operatorId);
                $operatorModel->deleteAccount();

                $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_remove_co_owner_success_message');
            } else {
                $pageParams['ERROR_MESSAGE'] = $this->f3->get('AdminApi_remove_co_owner_error_message');
            }
        }

        return $pageParams;
    }

    protected function makeOperatorCoOwner(\Models\Operator $operator, \Models\ApiKeys $key): void {
        $model = new \Models\ApiKeyCoOwner();
        $model->create($operator->id, $key->id);
    }

    protected function sendInvitationEmail(\Models\Operator $inviter, \Models\Operator $operator, \Models\ForgotPassword $forgotPassword): void {
        $site = \Utils\Variables::getHostWithProtocolAndBase();

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

    protected function sendChangeEmailEmail(\Models\Operator $currentOperator, \Models\ChangeEmail $changeEmailModel): void {
        $url = \Utils\Variables::getHostWithProtocolAndBase();

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
