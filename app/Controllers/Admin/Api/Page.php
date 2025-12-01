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

namespace Controllers\Admin\Api;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminApi';

    public function getPageParams(): array {
        $dataController = new Data();

        $currentOperator = \Utils\Routes::getCurrentRequestOperator();
        $operatorId = $currentOperator->id;

        $scheduledForEnrichment = $dataController->getScheduledForEnrichment();

        $pageParams = [
            'LOAD_AUTOCOMPLETE'         => true,
            'LOAD_DATATABLE'            => true,
            'HTML_FILE'                 => 'admin/api.html',
            'JS'                        => 'admin_api.js',
            'API_URL'                   => \Utils\Variables::getHostWithProtocolAndBase() . '/sensor/',
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
        }

        // set these params after proccessing POST request
        [$isOwner, $apiKeys] = $dataController->getOperatorApiKeysDetails($operatorId);
        $pageParams['IS_OWNER'] = $isOwner;
        $pageParams['API_KEYS'] = $apiKeys;
        $pageParams['NOT_CHECKED'] = $dataController->getNotCheckedEntitiesForLoggedUser();
        $pageParams['SCHEDULED_FOR_ENRICHMENT'] = $scheduledForEnrichment;

        return parent::applyPageParams($pageParams);
    }
}
