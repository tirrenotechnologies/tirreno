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

class PasswordRecovering extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'passwordRecovering';
    protected bool $allowGuest = true;

    protected function proceedPostRequest(): array {
        $this->assertCanEdit();

        $pageParams = [];

        $params = tirreno('utils')->render->extractRequestParams(['token', 'new-password', 'password-confirmation']);
        $errorCode = tirreno('utils')->validators->validatePasswordRecoveringPost($params);

        $pageParams['SUCCESS_CODE'] = 0;
        $pageParams['ERROR_CODE'] = $errorCode;

        if (!$errorCode) {
            $operatorId = tirreno('models')->forgotPassword->useByRenewKey(tirreno('request')->getUrlParam('renewKey'));
            $password = tirreno('utils')->conversion->getStringRequestParam('new-password');

            if ($operatorId) {
                tirreno('models')->operator->updatePassword($password, $operatorId);
                tirreno('models')->operator->activateByOperatorId($operatorId);
                $pageParams['SUCCESS_CODE'] = tirreno('utils')->errorCodes->ACCOUNT_ACTIVATED;
            } else {
                $pageParams['ERROR_CODE'] = tirreno('utils')->errorCodes->RENEW_KEY_IS_NOT_CORRECT;
            }
        }

        return $pageParams;
    }

    public function getPageParams(): array {
        $this->assertCanView();

        $pageParams = [
            'HTML_FILE'     => 'passwordRecovering.html',
            'INTERNAL_PAGE' => false,
        ];

        $errorCode = tirreno('utils')->validators->validatePasswordRecovering(tirreno('request')->getUrlParams());
        $pageParams['SUCCESS_CODE'] = $errorCode;

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest() : [];

        return array_merge($pageParams, $postParams);
    }
}
