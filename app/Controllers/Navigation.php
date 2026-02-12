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

namespace Tirreno\Controllers;

class Navigation extends Base {
    public \Tirreno\Views\Base $response;

    public function beforeroute(): void {
        // CSRF assignment in base page
        $this->response = new \Tirreno\Views\Frontend();
    }

    /**
     * kick start the View, which creates the response
     * based on our previously set content data.
     * finally echo the response or overwrite this method
     * and do something else with it.
     */
    public function afterroute(): void {
        echo $this->response->render();
    }

    public function visitSignupPage(): void {
        \Tirreno\Utils\Routes::redirectIfLogged();

        $pageController = new \Tirreno\Controllers\Pages\Signup();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitLoginPage(): void {
        \Tirreno\Utils\Routes::redirectIfLogged();

        $pageController = new \Tirreno\Controllers\Pages\Login();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitForgotPasswordPage(): void {
        \Tirreno\Utils\Routes::redirectIfLogged();

        if (!\Tirreno\Utils\Variables::getForgotPasswordAllowed()) {
            $this->f3->reroute('/');
        }

        $pageController = new \Tirreno\Controllers\Pages\ForgotPassword();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitPasswordRecoveringPage(): void {
        \Tirreno\Utils\Routes::redirectIfLogged();

        $pageController = new \Tirreno\Controllers\Pages\PasswordRecovering();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitLogoutPage(): void {
        \Tirreno\Utils\Routes::redirectIfUnlogged();

        $pageController = new \Tirreno\Controllers\Pages\Logout();
        $this->response->data = $pageController->getPageParams();
    }
}
