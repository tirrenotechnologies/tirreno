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

namespace Controllers\Admin\Bot;

class Data extends \Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return match (\Utils\Conversion::getStringRequestParam('cmd')) {
            'reenrichment' => $this->enrichEntity(),
            default => []
        };
    }

    public function enrichEntity(): array {
        $dataController = new \Controllers\Admin\Enrichment\Data();
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();

        $type       = \Utils\Conversion::getStringRequestParam('type');
        $search     = \Utils\Conversion::getStringRequestParam('search', true);
        $entityId   = \Utils\Conversion::getIntRequestParam('entityId', true);

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function checkIfOperatorHasAccess(int $botId, int $apiKey): bool {
        return (new \Models\Bot())->checkAccess($botId, $apiKey);
    }

    public function getBotDetails(int $botId, int $apiKey): array {
        return (new \Models\Bot())->getFullBotInfoById($botId, $apiKey);
    }

    public function isEnrichable(int $apiKey): bool {
        return (new \Models\ApiKeys())->attributeIsEnrichable('ua', $apiKey);
    }
}
