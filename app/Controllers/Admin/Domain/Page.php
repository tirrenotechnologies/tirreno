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

namespace Controllers\Admin\Domain;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminDomain';

    public function getPageParams(): array {
        $dataController = new Data();

        $domainId = $this->integerParam($this->f3->get('PARAMS.domainId'));
        $hasAccess = $dataController->checkIfOperatorHasAccess($domainId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $dataController->updateTotalsByDomainId($domainId);

        $domain = $dataController->getDomainDetails($domainId);
        $pageTitle = $this->getInternalPageTitleWithPostfix($domain['domain']);
        $isEnrichable = $dataController->isEnrichable();

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/domain.html',
            'DOMAIN'                        => $domain,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'JS'                            => 'admin_domain.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
            'OFFSET'                        => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'];
            // recall domain data
            $pageParams['DOMAIN'] = $dataController->getDomainDetails($domainId);
        }

        return parent::applyPageParams($pageParams);
    }
}
