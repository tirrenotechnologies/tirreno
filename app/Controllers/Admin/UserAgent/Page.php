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

namespace Tirreno\Controllers\Admin\UserAgent;

class Page extends \Tirreno\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminUserAgent';

    public function getPageParams(): array {
        $dataController = new Data();
        $apiKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $userAgentId = \Tirreno\Utils\Conversion::getIntUrlParam('userAgentId');
        $hasAccess = $dataController->checkIfOperatorHasAccess($userAgentId, $apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $userAgent = $dataController->getUserAgentDetails($userAgentId, $apiKey);
        $pageTitle = $this->getInternalPageTitleWithPostfix(strval($userAgent['id']));
        $isEnrichable = $dataController->isEnrichable($apiKey);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/userAgent.html',
            'USER_AGENT'                    => $userAgent,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'JS'                            => 'admin_user_agent.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            // recall userAgent data
            $pageParams['USER_AGENT'] = $dataController->getUserAgentDetails($userAgentId, $apiKey);
        }

        return parent::applyPageParams($pageParams);
    }
}
