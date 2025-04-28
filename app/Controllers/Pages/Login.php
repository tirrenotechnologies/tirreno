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

class Login extends Base {
    public $page = 'Login';

    public function getPageParams(): array {
        if (!\Utils\Variables::completedConfig()) {
            $this->f3->error(503);
        }

        $pageParams = [
            'HTML_FILE' => 'login.html',
            'JS' => 'user_main.js',
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $errorCode = $this->validate($params);

            if (!$errorCode) {
                $operatorsModel = new \Models\Operator();
                $operatorsModel->getActivatedByEmail($params['email']);

                if ($operatorsModel->loaded() && $operatorsModel->verifyPassword($params['password'])) {
                    $controller = new \Controllers\Admin\ReviewQueue\Navigation();
                    $controller->getNumberOfNotReviewedUsers(true, true);    // use cache, overall count

                    $this->f3->set('SESSION.active_user_id', $operatorsModel->id);
                    $extra = $this->f3->get('EXTRA_LOGIN');
                    if ($extra && is_callable($extra)) {
                        $params = $extra();
                    }
                    $this->f3->reroute('/');
                } else {
                    $errorCode = \Utils\ErrorCodes::EMAIL_OR_PASSWORD_IS_NOT_CORRECT;
                }
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

        if (!$params['email']) {
            return \Utils\ErrorCodes::EMAIL_DOES_NOT_EXIST;
        }

        if (!$params['password']) {
            return \Utils\ErrorCodes::PASSWORD_DOES_NOT_EXIST;
        }

        return false;
    }
}
