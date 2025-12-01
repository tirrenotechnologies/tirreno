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

class Login extends Base {
    public $page = 'Login';

    public function getPageParams(): array {
        if (!\Utils\Variables::completedConfig()) {
            $this->f3->error(503);
        }

        $pageParams = [
            'HTML_FILE'             => 'login.html',
            'JS'                    => 'user_main.js',
            'ALLOW_FORGOT_PASSWORD' => \Utils\Variables::getForgotPasswordAllowed(),
        ];

        if (!$this->isPostRequest()) {
            return parent::applyPageParams($pageParams);
        }

        $params = $this->extractRequestParams(['token', 'email', 'password']);
        $errorCode = \Utils\Validators::validateLogin($params);

        $pageParams['VALUES'] = $params;
        $pageParams['ERROR_CODE'] = $errorCode;

        if ($errorCode) {
            return parent::applyPageParams($pageParams);
        }

        \Utils\Updates::syncUpdates();

        $email      = \Utils\Conversion::getStringRequestParam('email');
        $password   = \Utils\Conversion::getStringRequestParam('password');

        $operatorsModel = new \Models\Operator();
        $operatorsModel->getActivatedByEmail($email);

        if ($operatorsModel->loaded() && $operatorsModel->verifyPassword($password)) {
            $this->f3->set('SESSION.active_user_id', $operatorsModel->id);

            // blacklist first because it uses review_queue_updated_at for cache check
            $controller = new \Controllers\Admin\Blacklist\Navigation();
            $controller->setBlacklistUsersCount(true);      // use cache

            $controller = new \Controllers\Admin\ReviewQueue\Navigation();
            $controller->setNotReviewedCount(true);         // use cache

            $pageParams['VALUES'] = \Utils\Routes::callExtra('LOGIN', $params) ?? $params;
            $this->f3->reroute('/');
        } else {
            $pageParams['VALUES'] = \Utils\Routes::callExtra('LOGIN_FAIL', $params) ?? $params;
            $pageParams['ERROR_CODE'] = \Utils\ErrorCodes::EMAIL_OR_PASSWORD_IS_NOT_CORRECT;
        }

        return parent::applyPageParams($pageParams);
    }
}
