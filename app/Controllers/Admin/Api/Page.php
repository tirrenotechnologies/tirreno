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

namespace Controllers\Admin\Api;

class Page extends \Controllers\Pages\Base {
    use \Traits\ApiKeys;

    public $page = 'AdminApi';

    public function getPageParams(): array {
        $dataController = new Data();

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;

        $scheduledForEnrichment = $dataController->getScheduledForEnrichment();

        $pageParams = [
            'LOAD_AUTOCOMPLETE'         => true,
            'LOAD_DATATABLE'            => true,
            'HTML_FILE'                 => 'admin/api.html',
            'JS'                        => 'admin_api.js',
            'API_URL'                   => \Utils\Variables::getSiteWithProtocol() . '/sensor/',
            'NOT_CHECKED'               => $dataController->getNotCheckedEntitiesForLoggedUser(),
            'SCHEDULED_FOR_ENRICHMENT'  => $scheduledForEnrichment,
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $params['id'] = $operatorId;

            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'] ?? null;
        }

        // set these params after proccessing POST request
        [$isOwner, $apiKeys] = $dataController->getOperatorApiKeysDetails($operatorId);
        $pageParams['IS_OWNER'] = $isOwner;
        $pageParams['API_KEYS'] = $apiKeys;

        return parent::applyPageParams($pageParams);
    }
}
