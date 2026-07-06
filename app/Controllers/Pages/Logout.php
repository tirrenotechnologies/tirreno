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

class Logout extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'logout';

    protected function proceedPostRequest(): array {
        $this->assertCanEdit();

        $pageParams = [];

        $params = tirreno('utils')->render->extractRequestParams(['token']);
        $errorCode = tirreno('utils')->access->CSRFTokenValid($params);

        if (!$errorCode) {
            tirreno('session')->clear();
            session_commit();
            tirreno('response')->redirect('/');
        }

        $pageParams['ERROR_CODE'] = $errorCode;

        return $pageParams;
    }

    public function getPageParams(): array {
        $this->assertCanView();

        $pageParams = [
            'HTML_FILE'     => 'logout.html',
            'JS'            => 'user_main.js',
            'INTERNAL_PAGE' => false,
        ];

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest() : [];

        return array_merge($pageParams, $postParams);
    }

    protected function getRequiredPermission(): int {
        return tirreno('utils')->constants->PAGE_VIEW_PERMISSION_ID;
    }
}
