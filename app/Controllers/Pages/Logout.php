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

class Logout extends Base {
    public $page = 'Logout';

    public function getPageParams() {
        $pageParams = [
            'HTML_FILE'     => 'logout.html',
            'JS'            => 'user_main.js',
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);

            if (!$errorCode) {
                $this->f3->clear('SESSION');
                session_commit();

                $this->f3->reroute('/');
            }

            $pageParams['ERROR_CODE'] = $errorCode;
        }

        return parent::applyPageParams($pageParams);
    }
}
