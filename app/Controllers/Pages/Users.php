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

class Users extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'users';

    protected function getPageParams(): array {
        $this->assertCanView();

        $ruleUid = tirreno('utils')->conversion->getStringRequestParam('ruleUid');
        $ruleUid = $ruleUid ? strtoupper($ruleUid) : null;

        return [
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'LOAD_CHOICES'          => true,
            'HTML_FILE'             => 'users.html',
            'JS'                    => 'users.js',
            'RULES'                 => tirreno('controllers')->rules->getAllRulesByApiKey($this->apiKey),
            'DEFAULT_RULE'          => $ruleUid,
            'INTERNAL_PAGE'         => true,
        ];
    }

    public function getChart(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getChart($this->apiKey) : [];
    }

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }
}
