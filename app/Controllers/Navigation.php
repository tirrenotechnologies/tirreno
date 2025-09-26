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

namespace Controllers;

class Navigation extends Base {
    public $response;

    public function beforeroute(): void {
        // CSRF assignment in base page
        $this->response = new \Views\Frontend();
    }

    /**
     * set a new view.
     */
    /* TODO: make sure that setView() is not needed
    public function setView(BaseView $view) {
        $this->response = $view;
    }*/

    /**
     * kick start the View, which creates the response
     * based on our previously set content data.
     * finally echo the response or overwrite this method
     * and do something else with it.
     */
    public function afterroute(): void {
        if (!$this->response) {
            trigger_error('No View has been set.');
        }

        echo $this->response->render();
    }

    public function visitSignupPage(): void {
        $this->redirectIfLogged();

        $pageController = new \Controllers\Pages\Signup();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitLoginPage(): void {
        $this->redirectIfLogged();

        $pageController = new \Controllers\Pages\Login();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitForgotPasswordPage(): void {
        $this->redirectIfLogged();

        if (!\Utils\Variables::getForgotPasswordAllowed()) {
            $this->f3->reroute('/');
        }

        $pageController = new \Controllers\Pages\ForgotPassword();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitPasswordRecoveringPage(): void {
        $this->redirectIfLogged();

        $pageController = new \Controllers\Pages\PasswordRecovering();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitLogoutPage(): void {
        $this->redirectIfUnlogged();

        $pageController = new \Controllers\Pages\Logout();
        $this->response->data = $pageController->getPageParams();
    }

    public function visitChangeEmailPage(): void {
        $pageController = new \Controllers\Pages\ChangeEmail();
        $this->response->data = $pageController->getPageParams();
    }
}
