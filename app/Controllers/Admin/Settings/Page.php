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

namespace Controllers\Admin\Settings;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminSettings';

    public function getPageParams(): array {
        $dataController = new Data();

        $currentOperator = \Utils\Routes::getCurrentRequestOperator();
        $operatorId = $currentOperator->id;

        [$isOwner, $apiKeys] = \Utils\ApiKeys::getOperatorApiKeys($operatorId);

        $pageParams = [
            'LOAD_DATATABLE'    => true,
            'LOAD_AUTOCOMPLETE' => true,
            'HTML_FILE'         => 'admin/settings.html',
            'JS'                => 'admin_settings.js',
            'TIMEZONES'         => \Utils\TimeZones::timeZonesList(),
            'CURRENT_VERSION'   => \Utils\VersionControl::fullVersionString(),
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            //$this->f3->reroute('/account');
        }

        // set shared_operatos and api_keys params after proccessing POST request
        $coOwners = $dataController->getSharedApiKeyOperators($operatorId);
        $pageParams['SHARED_OPERATORS'] = $coOwners;

        [$isOwner, $apiKeys] = \Utils\ApiKeys::getOperatorApiKeys($operatorId);

        $pageParams['IS_OWNER'] = $isOwner;
        $pageParams['API_KEYS'] = $apiKeys;

        $operatorModel = new \Models\Operator();
        $operatorModel->getOperatorById($operatorId);
        $pageParams['PROFILE'] = $operatorModel->cast();

        $changeEmailModel = new \Models\ChangeEmail();
        $changeEmailModel->getUnusedKeyByOperatorId($operatorId);
        $pageParams['PENDING_CONFIRMATION_EMAIL'] = $changeEmailModel->email ?? null;

        return parent::applyPageParams($pageParams);
    }
}
