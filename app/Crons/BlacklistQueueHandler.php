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

namespace Tirreno\Crons;

class BlacklistQueueHandler extends BaseQueue {
    public function process(): void {
        parent::baseProcess(tirreno('utils')->constants->BLACKLIST_QUEUE_ACTION_TYPE);
    }

    protected function processItem(array $item): void {
        $fraud = true;

        $items = tirreno('controllers')->user->setFraudFlag(
            $item['event_account'],
            $fraud,
            $item['key'],
        );

        $username = tirreno('models')->user->getUserById($item['event_account'], $item['key'])['userid'] ?? '';

        $msg = tirreno('utils')->systemMessages->syslogLine(10, 5, 'BlacklistQueue', 'blacklisted userid=' . $username);
        tirreno('router')->write(tirreno('storage')->get('LOGS') . 'blacklist.log', $msg . PHP_EOL, true);

        $key = tirreno('entities')->apiKey->getById($item['key']);

        if (!$key->skipBlacklistSync && $key->token) {
            $userEmail = tirreno('models')->user->getUserById($item['event_account'], $item['key'])['email'] ?? null;

            if ($userEmail !== null) {
                $hashes = tirreno('utils')->cron->getHashes($items, $userEmail);
                $errorMessage = tirreno('utils')->cron->sendBlacklistReportPostRequest($hashes, $key->token, $key->id);
                if (strlen($errorMessage) > 0) {
                    // TODO: log error into database?
                    $this->addLog('Enrichment API cURL ' . $errorMessage);
                    $this->addLog('Enrichment API cURL logged to database.');
                }
            }
        }
    }
}
