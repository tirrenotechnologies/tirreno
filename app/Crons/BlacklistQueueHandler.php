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

class BlacklistQueueHandler extends AbstractQueueCron {
    public function __construct() {
        parent::__construct();

        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Blacklist);
        $this->accountOperationQueueModel = new \Models\Queue\AccountOperationQueue($actionType);
    }

    public function processQueue(): void {
        if ($this->accountOperationQueueModel->isExecuting() && !$this->accountOperationQueueModel->unclog()) {
            $this->log('Blacklist queue is already being executed by another cron job.');

            return;
        }

        $this->processItems($this->accountOperationQueueModel);
    }

    protected function processItem(array $item): void {
        $fraud = true;

        $dataController = new \Controllers\Admin\User\Data();
        $items = $dataController->setFraudFlag(
            $item['event_account'],
            $fraud,
            $item['key'],
        );

        $creator = strval($item['creator']);
        $hashes = $this->getHashes($items);

        $model = new \Models\ApiKeys();
        $model->getKeyById($item['key']);

        $errorMessage = $model->skip_blacklist_sync ? $this->sendBlacklistReportPostRequest($creator, $hashes) : '';
        if (strlen($errorMessage) > 0) {
            // Log error to database
            \Utils\Logger::log('Fraud enrichment API curl error', $errorMessage);
            $this->log('Fraud enrichment API curl error logged to database.');
        }
    }

    /**
     * @param array<array{type: string, value: string}> $items
     */
    private function getHashes(array $items): array {
        return array_map(function ($item) {
            return [
                'type' => $item['type'],
                'value' => hash('sha256', $item['value']),
            ];
        }, $items);
    }

    /**
     * @param array<array{type: string, value: string}> $hashes
     */
    private function sendBlacklistReportPostRequest(string $reporter, array $hashes): string {
        $api = \Utils\Variables::getFraudEnrichmentApi();

        $postFields = [
            'reporter' => $reporter,
            'data' => $hashes,
        ];

        $options = [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
            ],
            'content' => \json_encode($postFields),
        ];

        /** @var array{request: array<string>, body: string, headers: array<string>, engine: string, cached: bool, error: string} $result */
        $result = \Web::instance()->request(
            url: sprintf('%s/global_alert_report', $api),
            options: $options,
        );

        return $result['error'];
    }
}
