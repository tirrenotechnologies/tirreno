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

namespace Tirreno\Controllers\Services;

class Settings extends \Tirreno\Controllers\Services\Base {
    public function getSharedApiKeyOperators(int $operatorId): array {
        return tirreno('models')->apiKeyCoOwner->getSharedApiKeyOperators($operatorId);
    }

    public function changePassword(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'current-password', 'new-password', 'password-confirmation']);
        $errorCode = tirreno('utils')->validators->validateChangePassword($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $password = tirreno('utils')->conversion->getStringRequestParam('new-password');
            $operatorId = tirreno('utils')->routes->getCurrentRequestOperator()->id;

            tirreno('models')->operator->updatePassword($password, $operatorId);

            // update operator obj
            tirreno('utils')->routes->setCurrentRequestOperator();

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('settings_changePassword_success_message');
        }

        return $pageParams;
    }

    public function changeEmail(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'email']);
        $errorCode = tirreno('utils')->validators->validateChangeEmail($params);

        if ($errorCode) {
            $pageParams['EMAIL_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = tirreno('utils')->routes->getCurrentRequestOperator()->id;
            $email = tirreno('utils')->conversion->getStringRequestParam('email');

            tirreno('models')->operator->updateEmail($email, $operatorId);

            // update operator obj
            tirreno('utils')->routes->setCurrentRequestOperator();

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('settings_changeEmail_success_message');
        }

        return $pageParams;
    }

    public function changeTimezone(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'timezone']);
        $errorCode = tirreno('utils')->validators->validateChangeTimezone($params);

        if ($errorCode) {
            $pageParams['TIME_ZONE_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $timezone = tirreno('utils')->conversion->getStringRequestParam('timezone');
            $operatorId = tirreno('utils')->routes->getCurrentRequestOperator()->id;

            tirreno('models')->operator->updateTimezone($timezone, $operatorId);

            // update operator in f3 hive for clock
            tirreno('utils')->routes->setCurrentRequestOperator();

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('settings_timezone_changeTimezone_success_message');
        }

        return $pageParams;
    }

    public function closeAccount(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token']);
        $errorCode = tirreno('utils')->validators->validateCloseAccount($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = tirreno('utils')->routes->getCurrentRequestOperator()->id;
            tirreno('models')->operator->closeAccount($operatorId);
            tirreno('models')->operator->removeData($operatorId);

            tirreno('session')->clear();
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            } else {
                session_commit();
            }

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('settings_closeAccount_success_message');
        }

        return $pageParams;
    }

    public function checkUpdates(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token']);
        $errorCode = tirreno('utils')->validators->validateCheckUpdates($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentVersion = tirreno('utils')->versionControl->versionString();

            $apiKey = tirreno('utils')->routes->getCurrentRequestApiKey()->id;

            $response = tirreno('utils')->network->sendApiRequest(null, '/version', 'GET', null, $apiKey);
            $code = $response->code;
            $result = $response->body;

            $statusCode = $code ?? 0;
            $errorMessage = $response->error ?? '';

            tirreno('log')->debug('checkUpdates /version API request with status code %d and response %s', $statusCode, json_encode($result));

            if (strlen($errorMessage) > 0 || $statusCode !== 200 || !is_array($result)) {
                $pageParams['ERROR_CODE'] = tirreno('utils')->errorCodes->ENRICHMENT_API_IS_NOT_AVAILABLE;
            } else {
                if (version_compare($currentVersion, $result['version'], '<')) {
                    $pageParams['SUCCESS_MESSAGE'] = sprintf('An update is available. Released date: %s.', $result['release_date']);
                } else {
                    $pageParams['SUCCESS_MESSAGE'] = 'Current version is up to date.';
                }
            }
        }

        return $pageParams;
    }

    public function updateNotificationPreferences(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'review-reminder-frequency']);
        $errorCode = tirreno('utils')->validators->validateUpdateNotificationPreferences($params);

        if ($errorCode) {
            $pageParams['PROFILE_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $reminder = tirreno('utils')->conversion->getStringRequestParam('review-reminder-frequency');
            $operatorId = tirreno('utils')->routes->getCurrentRequestOperator()->id;

            tirreno('models')->operator->updateNotificationPreferences($reminder, $operatorId);

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('settings_notificationPreferences_success_message');
        }

        return $pageParams;
    }

    public function changeRetentionPolicy(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'keyId', 'retention-policy']);
        $errorCode = tirreno('utils')->validators->validateRetentionPolicy($params);

        if ($errorCode) {
            $pageParams['RETENTION_POLICY_VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = tirreno('utils')->conversion->getIntRequestParam('keyId');
            $retentionPolicy = tirreno('utils')->conversion->getIntRequestParam('retention-policy');

            tirreno('models')->apiKeys->updateRetentionPolicy($retentionPolicy, $keyId);
            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('settings_retentionPolicy_changeTimezone_success_message');
        }

        return $pageParams;
    }

    public function inviteCoOwner(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'email']);
        $errorCode = tirreno('utils')->validators->validateInvitingCoOwner($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $currentOperator = tirreno('utils')->routes->getCurrentRequestOperator();
            $currentOperatorId = $currentOperator->id;

            $apiKey = tirreno('utils')->routes->getCurrentRequestApiKey();

            $params['timezone'] = 'UTC';
            $invitedOperatorId = tirreno('models')->operator->insertRecord(null, $params['email'], 'UTC');

            $renewKey = tirreno('models')->forgotPassword->insertRecord($invitedOperatorId);

            $this->makeOperatorCoOwner($invitedOperatorId, $apiKey->id);
            $this->sendInvitationEmail($params['email'], $currentOperatorId, $renewKey);

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('api_add_co_owner_success_message');
        }

        return $pageParams;
    }

    public function removeCoOwner(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'operatorId']);
        $errorCode = tirreno('utils')->validators->validateRemovingCoOwner($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = tirreno('utils')->conversion->getIntRequestParam('operatorId');

            $keyId = tirreno('models')->apiKeyCoOwner->getCoOwnershipKeyId($operatorId);
            $apiKey = tirreno('utils')->routes->getCurrentSessionApiKey();

            if ($apiKey->id === $keyId && tirreno('utils')->routes->getCurrentRequestOperator()->id === $apiKey->creator) {
                tirreno('models')->apiKeyCoOwner->deleteCoOwnership($operatorId);

                tirreno('models')->operator->deleteAccount($operatorId);

                $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('api_remove_co_owner_success_message');
            } else {
                $pageParams['ERROR_MESSAGE'] = tirreno('storage')->get('api_remove_co_owner_error_message');
            }
        }

        return $pageParams;
    }

    protected function makeOperatorCoOwner(int $operatorId, int $apiKey): void {
        tirreno('models')->apiKeyCoOwner->insertRecord($operatorId, $apiKey);
    }

    protected function sendInvitationEmail(string $email, int $inviterId, string $renewKey): void {
        $toAddress = $email;

        $inviter = tirreno('entities')->operator->getById($inviterId);

        $site = tirreno('utils')->variables->getHostWithProtocolAndBase();

        $inviterDisplayName = $inviter->email;
        if ($inviter->firstname && $inviter->lastname) {
            $inviterDisplayName = sprintf('%s %s (%s)', $inviter->firstname, $inviter->lastname, $inviterDisplayName);
        }

        $toName = null;
        //$toAddress = $operator->email;

        $subject = tirreno('storage')->get('api_invitation_email_subject');
        $message = tirreno('storage')->get('api_invitation_email_body');

        $renewUrl = sprintf('%s/password-recovering/%s', $site, $renewKey);
        $message = sprintf($message, $inviterDisplayName, $renewUrl);

        tirreno('utils')->mailer->send($toName, $toAddress, $subject, $message);
    }
}
