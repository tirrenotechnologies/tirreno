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

namespace Controllers\Admin\Users;

class Page extends \Controllers\Pages\Base {
    use \Traits\ApiKeys;

    public $page = 'AdminUsers';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminUsers_search_placeholder');
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $rulesController = new \Controllers\Admin\Rules\Data();

        $ruleUid = $this->f3->get('GET.ruleUid');
        $ruleUid = $ruleUid !== null ? strtoupper($ruleUid) : null;

        $pageParams = [
            'SEARCH_PLACEHOLDER'    => $searchPlacholder,
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'LOAD_CHOICES'          => true,
            'HTML_FILE'             => 'admin/users.html',
            'JS'                    => 'admin_users.js',
            'RULES'                 => $rulesController->getAllRulesByApiKey($apiKey),
            'DEFAULT_RULE'          => $ruleUid,
            'OFFSET'                => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        return parent::applyPageParams($pageParams);
    }
}
