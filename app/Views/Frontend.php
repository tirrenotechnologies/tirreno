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

namespace Tirreno\Views;

class Frontend extends Base {
    public function render(): string|false|null {
        if ($this->data) {
            tirreno('router')->mset($this->data);
        }

        tirreno('utils')->routes->callExtra('FRONTEND_VIEW');

        // Use anti-CSRF token in templates.
        tirreno('storage')->set('CSRF', tirreno('session')->get('csrf'));

        $tpl = tirreno('storage')->get('TPL') ?? null;
        if ($tpl) {
            $tpl::registerExtends();
        }

        return \Template::instance()->render('templates/layout.html');
    }
}
