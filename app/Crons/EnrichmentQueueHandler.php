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

namespace Crons;

class EnrichmentQueueHandler extends BaseQueue {
    private \Controllers\Admin\Enrichment\Data $controller;

    public function __construct() {
        $this->controller = new \Controllers\Admin\Enrichment\Data();
    }

    public function process(): void {
        parent::baseProcess(\Utils\Constants::get('ENRICHMENT_QUEUE_ACTION_TYPE'));
    }

    protected function processItem(array $item): void {
        $start = time();
        $apiKey = $item['key'];
        $userId = $item['event_account'];

        $entities = $this->controller->getNotCheckedEntitiesByUserId($userId, $apiKey);

        $subscriptionKey = (new \Models\ApiKeys())->getKeyById($apiKey)->token;

        // TODO: check key ?
        $this->addLog(sprintf('Items to enrich for account %s: %s.', $userId, json_encode($entities)));

        $summary = [];
        $success = 0;
        $failed = 0;

        foreach ($entities as $type => $items) {
            if (count($items)) {
                $summary[$type] = count($items);
            }
            foreach ($items as $item) {
                $result = $this->controller->enrichEntity($type, null, $item, $apiKey, $subscriptionKey);
                if (isset($result['ERROR_CODE'])) {
                    $failed += 1;
                } else {
                    $success += 1;
                }
            }
        }

        // TODO: if failed !== 0 add to queue again?
        // TODO: recalculate score after all?
        $this->addLog(sprintf('Enrichment for account %s: %s enriched, %s failed in %s s (%s).', $userId, $success, $failed, time() - $start, json_encode($summary)));
    }
}
