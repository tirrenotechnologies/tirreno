<?php

/**
 * tirreno ~ open security analytics
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

namespace Controllers\Admin\Rules;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminRules';

    public function getPageParams(): array {
        $dataController = new Data();
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $rules = $dataController->getRulesForApiKey($apiKey);
        $searchPlacholder = $this->f3->get('AdminRules_search_placeholder');

        $currentOperator = \Utils\Routes::getCurrentRequestOperator();
        $operatorId = $currentOperator->id;

        $ruleValues = [
            ['value' => -20, 'text' => $this->f3->get('AdminRules_weight_minus20')],
            ['value' => 0,   'text' => $this->f3->get('AdminRules_weight_0')],
            ['value' => 10,  'text' => $this->f3->get('AdminRules_weight_10')],
            ['value' => 20,  'text' => $this->f3->get('AdminRules_weight_20')],
            ['value' => 70,  'text' => $this->f3->get('AdminRules_weight_70')],
        ];

        $pageParams = [
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'admin/rules.html',
            'JS'                    => 'admin_rules.js',
            'RULE_VALUES'           => $ruleValues,
            'RULES'                 => $rules,
            'SEARCH_PLACEHOLDER'    => $searchPlacholder,
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['RULES'] = $dataController->getRulesForApiKey($apiKey);
        }

        // set api_keys param after proccessing POST request
        [$isOwner, $apiKeys] = \Utils\ApiKeys::getOperatorApiKeys($operatorId);

        $pageParams['IS_OWNER'] = $isOwner;
        $pageParams['API_KEYS'] = $apiKeys;

        return parent::applyPageParams($pageParams);
    }
}
