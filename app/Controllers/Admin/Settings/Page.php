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

namespace Controllers\Admin\Settings;

class Page extends \Controllers\Pages\Base {
    use \Traits\ApiKeys;

    public $page = 'AdminSettings';

    public function getPageParams(): array {
        $dataController = new Data();

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;

        [$isOwner, $apiKeys] = $dataController->getOperatorApiKeys($operatorId);

        $pageParams = [
            'LOAD_DATATABLE'    => true,
            'LOAD_AUTOCOMPLETE' => true,
            'HTML_FILE'         => 'admin/settings.html',
            'JS'                => 'admin_settings.js',
            'API_KEYS'          => $apiKeys,
            'IS_OWNER'          => $isOwner,
            'CURRENT_VERSION'   => \Utils\VersionControl::fullVersionString(),
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $params['id'] = $operatorId;
            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'];

            //$this->f3->reroute('/account');
        }

        // set shared_operatos param after proccessing POST request
        $coOwners = $dataController->getSharedApiKeyOperators($operatorId);
        $pageParams['SHARED_OPERATORS'] = $coOwners;

        $operatorModel = new \Models\Operator();
        $operatorModel->getOperatorById($operatorId);
        $pageParams['PROFILE'] = $operatorModel->cast();

        $changeEmailModel = new \Models\ChangeEmail();
        $changeEmailModel->getUnusedKeyByOperatorId($operatorId);
        $pageParams['PENDING_CONFIRMATION_EMAIL'] = $changeEmailModel->email ?? null;

        return parent::applyPageParams($pageParams);
    }
}
