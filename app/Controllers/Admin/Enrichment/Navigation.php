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

namespace Controllers\Admin\Enrichment;

class Navigation extends \Controllers\Base {
    use \Traits\ApiKeys;
    use \Traits\Navigation;

    public function enrichEntity(): array {
        $dataController = new Data();
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $enrichmentKey = $this->getCurrentOperatorEnrichmentKeyString();
        $params = $this->f3->get('POST');
        $type = $params['type'];
        $search = $params['search'] ?? null;
        $entityId = $params['entityId'] ?? null;

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function getNotCheckedEntitiesCount(): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        return $apiKey ? (new Data())->getNotCheckedEntitiesCount($apiKey) : [];
    }
}
