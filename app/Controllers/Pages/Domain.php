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

namespace Tirreno\Controllers\Pages;

class Domain extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'domain';

    protected function proceedPostRequest(int $apiKey): array {
        $this->assertCanEdit();

        return match (tirreno('utils')->conversion->getStringRequestParam('cmd')) {
            'reenrichment' => tirreno('controllers')->enrichment->enrichEntityFromRequest($apiKey),
            default => []
        };
    }

    protected function getPageParams(): array {
        $this->assertCanView();

        $domainId   = tirreno('utils')->conversion->getIntUrlParam('domainId');
        $hasAccess  = tirreno('controllers')->domain->checkIfOperatorHasAccess($domainId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest($this->apiKey) : [];

        $domain         = tirreno('controllers')->domain->getDomainDetails($domainId, $this->apiKey);
        $isEnrichable   = tirreno('controllers')->domain->isEnrichable($this->apiKey);
        $pageTitle      = tirreno('utils')->render->getInternalPageTitleWithPostfix($domain['domain']);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'domain.html',
            'DOMAIN'                        => $domain,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'JS'                            => 'domain.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
            'INTERNAL_PAGE'                 => true,
        ];

        return array_merge($pageParams, $postParams);
    }

    public function getDomainDetails(): array {
        $this->assertCanView();

        $domainId = tirreno('utils')->conversion->getIntRequestParam('domainId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($domainId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        return $this->controller->getDomainDetails($domainId, $this->apiKey);
    }
}
