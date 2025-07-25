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

abstract class Base {
    use \Traits\Db;
    use \Traits\Debug;

    protected $f3;

    public function __construct() {
        $this->f3 = \Base::instance();

        $keepSessionInDb = $this->f3->get('KEEP_SESSION_IN_DB') ?? null;
        if (!$this->connectToDb($keepSessionInDb)) {
            $this->f3->error(404);
        }

        //Determine current user
        $currentOperator = $this->getLoggedInOperator();

        $this->f3->set('CURRENT_USER', $currentOperator);

        //Set CSRF token
        //$rnd = mt_rand();
        //$this->f3->CSRF = sprintf('%s.%s', $this->f3->SEED, $this->f3->hash($rnd));
    }

    /**
     * @todo This is only used at one place. We should remove or generalise it.
     */
    public function validateCsrfToken(): int|bool {
        $csrf = $this->f3->get('SESSION.csrf');
        $token = $this->f3->get('REQUEST.token');

        if (!isset($token) || $token === '' || !isset($csrf) || $csrf === '' || $token !== $csrf) {
            return \Utils\ErrorCodes::CSRF_ATTACK_DETECTED;
        }

        return false;
    }
}
