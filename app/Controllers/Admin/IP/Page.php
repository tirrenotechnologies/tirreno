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

namespace Controllers\Admin\IP;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminIp';

    public function getPageParams(): array {
        $dataController = new Data();
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $ipId = \Utils\Conversion::getIntUrlParam('ipId');
        $hasAccess = $dataController->checkIfOperatorHasAccess($ipId, $apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $ip = $dataController->getFullIpInfoById($ipId, $apiKey);
        $pageTitle = $this->getInternalPageTitleWithPostfix($ip['ip']);
        $isEnrichable = $dataController->isEnrichable($apiKey);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/ip.html',
            'PAGE_TITLE'                    => $pageTitle,
            'IP'                            => $ip,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'JS'                            => 'admin_ip.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            // recall ip data
            $pageParams['IP'] = $dataController->getFullIpInfoById($ipId, $apiKey);
        }

        return parent::applyPageParams($pageParams);
    }
}
