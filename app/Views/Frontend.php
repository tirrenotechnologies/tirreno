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

namespace Views;

class Frontend extends Base {
    public function render(): string|false|null {
        parent::render();

        if ($this->data) {
            $this->f3->mset($this->data);
        }

        // Use anti-CSRF token in templates.
        $this->f3->CSRF = $this->f3->get('SESSION.csrf');

        $tpl = $this->f3->get('TPL') ?? null;
        if ($tpl) {
            $tpl::registerExtends();
        }

        return \Template::instance()->render('templates/layout.html');
    }
}
