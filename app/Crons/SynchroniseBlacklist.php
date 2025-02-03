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

class SynchroniseBlacklist extends AbstractCron {
    public function synchroniseBlacklist(): void {
        $this->log('Start synchronising blacklist.');

        $lastSeenEmails = $emailHashes = $hashes = [];

        try {
            $totalHashesCnt = 0;
            $alertListedCnt = 0;

            $emailModel = new \Models\Email();
            $lastSeenEmails = $emailModel->getSeenInLastDay(includeAlertListed: false, includeWithoutHash: false, includeWithBlacklistSyncSkipped: false);

            // perform data transformation
            $groupedEmails = [];
            foreach ($lastSeenEmails as $item) {
                if (!array_key_exists($item['key'], $groupedEmails)) {
                    $groupedEmails[(int) $item['key']] = [];
                }
                $groupedEmails[(int) $item['key']][] = ['value' => $item['hash'], 'type' => 'email'];
            }
            if (count($groupedEmails)) {
                $model = new \Models\ApiKeys();

                // use different access keys for email lists grouped by apiKey
                foreach ($groupedEmails as $key => $items) {
                    $keyModel = $model->getKeyById($key);
                    if ($keyModel->skip_blacklist_sync) {
                        $this->log(sprintf('Skip synchronising blacklist for key %s.', strval($key)));
                        continue;
                    }

                    $subscriptionKey = $keyModel->token;
                    if ($subscriptionKey === null) {
                        $this->log(sprintf('Skip synchronising blacklist for key %s due to missing subscription key.', strval($key)));
                        continue;
                    }

                    $totalHashesCnt += count($items);
                    [$jsonResponse, $errorMessage] = $this->sendGlobalAlertLookupRequest($items, $subscriptionKey);
                    if (strlen($errorMessage) > 0) {
                        throw new \Exception('Enrichment API curl error for key ' . strval($key) . ': ' . $errorMessage);
                    }

                    if (!is_array($jsonResponse)) {
                        throw new \Exception('Enrichment API response is not an array for key ' . strval($key));
                    }

                    $hashes = $this->getAlertListedItemsHashesFromResponse($jsonResponse);
                    $alertListedCnt += count($hashes);

                    if (count($hashes)) {
                        $emailModel->updateAlertListedByHashes(hashes: $hashes, alertListed: true, apiKey: $key);
                    }
                }
            }

            $this->log(sprintf(
                'Synchronised %s emails and updated blacklist status of %s emails for %s keys.',
                $totalHashesCnt,
                $alertListedCnt,
                count($groupedEmails),
            ));
        } catch (\Exception $e) {
            \Utils\Logger::log('Error occurred during blacklist synchronization', $e->getMessage());
        }
    }

    private function sendGlobalAlertLookupRequest(array $hashes, string $subscriptionKey): array {
        $api = \Utils\Variables::getEnrichtmentApi();

        $postFields = [
            'data' => $hashes,
        ];

        $options = [
            'method' => 'POST',
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $subscriptionKey,
            ],
            'content' => \json_encode($postFields),
        ];

        /** @var array{request: array<string>, body: string, headers: array<string>, engine: string, cached: bool, error: string} $result */
        $result = \Web::instance()->request(
            url: sprintf('%s/query', $api),
            options: $options,
        );

        $errorMessage = $result['error'];
        $jsonResponse = json_decode($result['body'], true);

        return [$jsonResponse, $errorMessage];
    }

    private function getAlertListedItemsHashesFromResponse(array $response): array {
        $alertListedItems = array_filter($response, function ($item) {
            return ($item['alert_list'] ?? null) === true && isset($item['hash']) && $item['hash'] !== '';
        });

        return array_map(function ($item) {
            return $item['hash'];
        }, $alertListedItems);
    }
}
