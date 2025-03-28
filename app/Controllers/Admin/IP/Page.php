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

namespace Controllers\Admin\IP;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminIp';

    public function getPageParams(): array {
        $dataController = new Data();
        $ipId = $this->integerParam($this->f3->get('PARAMS.ipId'));
        $hasAccess = $dataController->checkIfOperatorHasAccess($ipId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $ip = $dataController->getFullIpInfoById($ipId);
        $pageTitle = $this->getInternalPageTitleWithPostfix($ip['ip']);
        $isEnrichable = $dataController->isEnrichable();

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
            'OFFSET'                        => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'];
            // recall ip data
            $pageParams['IP'] = $dataController->getFullIpInfoById($ipId);
        }

        return parent::applyPageParams($pageParams);
    }
}
