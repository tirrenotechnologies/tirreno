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

class Api extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'api';

    protected function proceedPostRequest(int $apiKey): array {
        $this->assertCanEdit();

        return match (tirreno('utils')->conversion->getStringRequestParam('cmd')) {
            'resetKey'          => tirreno('controllers')->api->resetApiKey($apiKey),
            'updateApiUsage'    => tirreno('controllers')->api->updateApiUsage($apiKey),
            'enrichAll'         => tirreno('controllers')->api->enrichAll($apiKey),
            default => []
        };
    }

    protected function getPageParams(): array {
        $this->assertCanView();

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest($this->apiKey) : [];

        $scheduledForEnrichment = tirreno('controllers')->api->getScheduledForEnrichment($this->apiKey);
        [$isOwner, $apiKeys] = tirreno('controllers')->api->getOperatorApiKeysDetails($this->operator->id);

        $pageParams = [
            'LOAD_AUTOCOMPLETE'         => true,
            'LOAD_DATATABLE'            => true,
            'HTML_FILE'                 => 'api.html',
            'JS'                        => 'api.js',
            'API_URL'                   => tirreno('utils')->variables->getHostWithProtocolAndBase() . '/sensor/',
            'NOT_CHECKED'               => tirreno('controllers')->api->getNotCheckedEntities($this->apiKey),
            'SCHEDULED_FOR_ENRICHMENT'  => $scheduledForEnrichment,
            'IS_OWNER'                  => $isOwner,
            'API_KEYS'                  => $apiKeys,
            'INTERNAL_PAGE'             => true,
        ];

        return array_merge($pageParams, $postParams);
    }

    public function getUsageStats(): array {
        $this->assertCanView();

        return $this->operator->isLoggedIn() ? $this->controller->getUsageStats($this->operator->id) : [];
    }

    public function getNotCheckedEntitiesCount(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getNotCheckedEntitiesCount($this->apiKey) : [];
    }
}
