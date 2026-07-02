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

class Login extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'login';
    protected bool $allowGuest = true;

    protected function proceedPostRequest(): array {
        $this->assertCanEdit();

        $pageParams = [];

        $params = tirreno('utils')->render->extractRequestParams(['token', 'email', 'password']);
        $errorCode = tirreno('utils')->validators->validateLogin($params);

        $pageParams['VALUES'] = $params;
        $pageParams['ERROR_CODE'] = $errorCode;

        if ($errorCode) {
            return $pageParams;
        }

        tirreno('utils')->updates->syncUpdates();

        $email      = tirreno('utils')->conversion->getStringRequestParam('email');
        $password   = tirreno('utils')->conversion->getStringRequestParam('password');

        $operatorId = tirreno('models')->operator->getActivatedByEmail($email);

        if ($operatorId && $operatorId > tirreno('utils')->constants->RESERVED_OPERATOR_IDS && tirreno('models')->operator->verifyPassword($password, $operatorId)) {
            $this->proceedSuccessfulLogin($operatorId);
            tirreno('response')->redirect('/');
        } else {
            $pageParams['VALUES'] = tirreno('utils')->routes->callExtra('LOGIN_FAIL', $params) ?? $params;
            $pageParams['ERROR_CODE'] = tirreno('utils')->errorCodes->EMAIL_OR_PASSWORD_IS_NOT_CORRECT;
        }

        return $pageParams;
    }

    protected function proceedSuccessfulLogin(int $operatorId): void {
        tirreno('session')->clear();
        session_commit();

        tirreno('session')->set('active_user_id', $operatorId);
        tirreno('session')->set('active_key_id', tirreno('utils')->apiKeys->getFirstKeyByOperatorId($operatorId));

        tirreno('utils')->routes->setCurrentRequestOperator();
        tirreno('utils')->routes->setCurrentRequestApiKey();

        $this->apiKey = tirreno('utils')->access->getCurrentOperatorApiKeyId();

        // blacklist first because it uses review_queue_updated_at for cache check
        tirreno('controllers')->blacklist->setBlacklistUsersCount(true, $this->apiKey);        // use cache
        tirreno('controllers')->reviewQueue->setNotReviewedCount(true, $this->apiKey);         // use cache
    }

    protected function isAllowed(): bool {
        if (!tirreno('utils')->variables->completedConfig()) {
            tirreno('response')->error(422);
        }

        return parent::isAllowed();
    }

    public function getPageParams(): array {
        $this->assertCanView();

        $pageParams = [
            'HTML_FILE'             => 'login.html',
            'JS'                    => 'user_main.js',
            'ALLOW_FORGOT_PASSWORD' => tirreno('utils')->variables->getForgotPasswordAllowed(),
            'INTERNAL_PAGE'         => false,
        ];

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest() : [];

        return array_merge($pageParams, $postParams);
    }
}
