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

namespace Controllers\Admin\Rules;

class Page extends \Controllers\Pages\Base {
    use \Traits\ApiKeys;

    public $page = 'AdminRules';

    public function getPageParams(): array {
        $dataController = new Data();
        $rules = $dataController->getRulesForLoggedUser();
        $searchPlacholder = $this->f3->get('AdminRules_search_placeholder');

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;

        [$isOwner, $apiKeys] = $dataController->getOperatorApiKeys($operatorId);

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
            'IS_OWNER'              => $isOwner,
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $params['id'] = $operatorId;
            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'];
        }

        $pageParams['API_KEYS'] = $apiKeys;

        return parent::applyPageParams($pageParams);
    }
}
