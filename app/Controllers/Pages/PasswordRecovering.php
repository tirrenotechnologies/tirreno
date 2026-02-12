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

namespace Tirreno\Controllers\Pages;

class PasswordRecovering extends Base {
    public ?string $page = 'PasswordRecovering';

    public function getPageParams(): array {
        $pageParams = [
            'HTML_FILE' => 'passwordRecovering.html',
        ];

        $errorCode = \Tirreno\Utils\Validators::validatePasswordRecovering($this->f3->get('PARAMS'));
        $pageParams['SUCCESS_CODE'] = $errorCode;

        if ($this->isPostRequest()) {
            $params = $this->extractRequestParams(['token', 'new-password', 'password-confirmation']);
            $errorCode = \Tirreno\Utils\Validators::validatePasswordRecoveringPost($params);

            $pageParams['SUCCESS_CODE'] = 0;
            $pageParams['ERROR_CODE'] = $errorCode;

            if (!$errorCode) {
                $forgotPasswordModel = new \Tirreno\Models\ForgotPassword();
                $operatorId = $forgotPasswordModel->useByRenewKey($this->f3->get('PARAMS.renewKey'));

                $password = \Tirreno\Utils\Conversion::getStringRequestParam('new-password');

                $model = new \Tirreno\Models\Operator();
                $model->updatePassword($password, $operatorId);
                $model->activateByOperatorId($operatorId);

                $pageParams['SUCCESS_CODE'] = \Tirreno\Utils\ErrorCodes::ACCOUNT_ACTIVATED;
            }
        }

        return parent::applyPageParams($pageParams);
    }
}
