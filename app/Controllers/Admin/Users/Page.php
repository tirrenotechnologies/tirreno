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
        $pageParams = [
            'SEARCH_PLACEHOLDER'    => $searchPlacholder,
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'LOAD_CHOICES'          => true,
            'HTML_FILE'             => 'admin/users.html',
            'JS'                    => 'admin_users.js',
            'RULES'                 => $rulesController->getAllRulesByApiKey($apiKey),
            'OFFSET'                => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        $pageParams = parent::applyPageParams($pageParams);

        return $this->applySessionMessageToPageParams($pageParams);
    }

    private function applySessionMessageToPageParams(array $pageParams): array {
        $code = $this->f3->get('SESSION.deleteUserOperationCode');

        if ($code) {
            $this->f3->clear('SESSION.deleteUserOperationCode');

            if (!isset($pageParams['SYSTEM_MESSAGES'])) {
                $pageParams['SYSTEM_MESSAGES'] = [];
            }

            $message = sprintf('error_%s', $code);
            $message = $this->f3->get($message);

            $createdAt = date('Y-m-d H:i:s');
            $pageParams['SYSTEM_MESSAGES'][] = [
                'text' => $message,
                'created_at' => $createdAt,
            ];
        }

        return $pageParams;
    }
}
