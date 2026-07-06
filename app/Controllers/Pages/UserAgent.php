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

class UserAgent extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'userAgent';

    public function proceedPostRequest(int $apiKey): array {
        $this->assertCanEdit();

        return match (tirreno('utils')->conversion->getStringRequestParam('cmd')) {
            'reenrichment' => tirreno('controllers')->enrichment->enrichEntity($apiKey),
            default => []
        };
    }

    protected function getPageParams(): array {
        $this->assertCanView();

        $userAgentId    = tirreno('utils')->conversion->getIntUrlParam('userAgentId');
        $hasAccess      = tirreno('controllers')->userAgent->checkIfOperatorHasAccess($userAgentId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest($this->apiKey) : [];

        $userAgent      = tirreno('controllers')->userAgent->getUserAgentDetails($userAgentId, $this->apiKey);
        $pageTitle      = tirreno('utils')->render->getInternalPageTitleWithPostfix(strval($userAgent['id']));
        $isEnrichable   = tirreno('controllers')->userAgent->isEnrichable($this->apiKey);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'userAgent.html',
            'USER_AGENT'                    => $userAgent,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'JS'                            => 'user_agent.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
            'INTERNAL_PAGE'                 => true,
        ];

        return array_merge($pageParams, $postParams);
    }

    public function getUserAgentDetails(): array {
        $this->assertCanView();

        $userAgentId = tirreno('utils')->conversion->getIntRequestParam('userAgentId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($userAgentId, $this->apiKey);
        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        return $this->controller->getUserAgentDetails($userAgentId, $this->apiKey);
    }
}
