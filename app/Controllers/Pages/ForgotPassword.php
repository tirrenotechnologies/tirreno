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

namespace Controllers\Pages;

class ForgotPassword extends Base {
    public $page = 'ForgotPassword';

    public function getPageParams(): array {
        if (!\Utils\Variables::getForgotPasswordAllowed()) {
            return [];
        }

        $pageParams = [
            'HTML_FILE' => 'forgotPassword.html',
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $errorCode = $this->validate($params);

            if (!$errorCode) {
                $operatorModel = new \Models\Operator();
                $operatorModel->getActivatedByEmail($params['email']);

                if ($operatorModel->loaded()) {
                    // Create forgot password record.
                    $forgotPasswordModel = new \Models\ForgotPassword();
                    $forgotPasswordModel->add($operatorModel->id);

                    // Send forgot password email.
                    $this->sendPasswordRenewEmail($operatorModel, $forgotPasswordModel);
                }

                // Random sleep between 0.5 and 1 second to prevent timing attacks.
                usleep(rand(500000, 1000000));

                // Always report back that the email was sent.
                $pageParams['SUCCESS_CODE'] = \Utils\ErrorCodes::RENEW_KEY_CREATED;
            }

            $pageParams['VALUES'] = $params;
            $pageParams['ERROR_CODE'] = $errorCode;
        }

        return parent::applyPageParams($pageParams);
    }

    private function validate(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        if (!($params['email'] ?? null)) {
            return \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST;
        }

        return false;
    }

    private function sendPasswordRenewEmail(\Models\Operator $operatorModel, \Models\ForgotPassword $forgotPasswordModel): void {
        $url = \Utils\Variables::getSiteWithProtocol();

        $toName = $operatorModel->firstname;
        $toAddress = $operatorModel->email;
        $renewKey = $forgotPasswordModel->renew_key;

        $subject = $this->f3->get('ForgotPassowrd_renew_password_subject');
        $message = $this->f3->get('ForgotPassowrd_renew_password_body');

        $renewUrl = sprintf('%s/password-recovering/%s', $url, $renewKey);
        $message = sprintf($message, $renewUrl);

        \Utils\Mailer::send($toName, $toAddress, $subject, $message);
    }
}
