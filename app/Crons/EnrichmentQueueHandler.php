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

namespace Crons;

class EnrichmentQueueHandler extends AbstractQueueCron {
    private \Models\ApiKeys $apiKeysModel;
    private \Controllers\Admin\Enrichment\Data $controller;

    public function __construct() {
        parent::__construct();

        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Enrichment);
        $this->accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

        $this->apiKeysModel = new \Models\ApiKeys();
        $this->controller = new \Controllers\Admin\Enrichment\Data();
    }

    public function processQueue(): void {
        if ($this->accountOpQueueModel->isExecuting() && !$this->accountOpQueueModel->unclog()) {
            $this->log('Enrchment queue is already being executed by another cron job.');
        } else {
            $this->processItems($this->accountOpQueueModel);
        }
    }

    protected function processItem(array $item): void {
        $start = time();
        $apiKey = $item['key'];
        $userId = $item['event_account'];

        $subscriptionKey = $this->apiKeysModel->getKeyById($apiKey)->token;
        $entities = $this->controller->getNotCheckedEntitiesByUserId($userId, $apiKey);

        // TODO: check key ?
        $this->log(sprintf('Items to enrich for account %s: %s.', $userId, json_encode($entities)));

        $summary = [];
        $success = 0;
        $failed = 0;

        foreach ($entities as $type => $items) {
            if (count($items)) {
                $summary[$type] = count($items);
            }
            foreach ($items as $idx => $item) {
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
        $this->log(sprintf('Enrichment for account %s: %s enriched, %s failed in %s s (%s).', $userId, $success, $failed, time() - $start, json_encode($summary)));
    }
}
