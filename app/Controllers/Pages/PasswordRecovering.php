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

namespace Controllers\Pages;

class PasswordRecovering extends Base {
    public $page = 'PasswordRecovering';

    public function getPageParams(): array {
        $pageParams = [
            'HTML_FILE' => 'passwordRecovering.html',
        ];

        $errorCode = \Utils\Validators::validatePasswordRecovering($this->f3->get('PARAMS'));
        $pageParams['SUCCESS_CODE'] = $errorCode;

        if ($this->isPostRequest()) {
            $params = $this->extractRequestParams(['token', 'new-password', 'password-confirmation']);
            $errorCode = \Utils\Validators::validatePasswordRecoveringPost($params);

            $pageParams['SUCCESS_CODE'] = 0;
            $pageParams['ERROR_CODE'] = $errorCode;

            if (!$errorCode) {
                $forgotPasswordModel = new \Models\ForgotPassword();
                $forgotPasswordModel->getUnusedByRenewKey($this->f3->get('PARAMS.renewKey'));
                $operatorId = $forgotPasswordModel->operator_id;

                $forgotPasswordModel->deactivate();

                $password = \Utils\Conversion::getStringRequestParam('new-password');

                $operatorModel = new \Models\Operator();
                $operatorModel->updatePassword($password, $operatorId);
                $operatorModel->activateByOperator($operatorId);

                $pageParams['SUCCESS_CODE'] = \Utils\ErrorCodes::ACCOUNT_ACTIVATED;
            }
        }

        return parent::applyPageParams($pageParams);
    }
}
