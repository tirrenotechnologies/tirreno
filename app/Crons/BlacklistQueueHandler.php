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

class BlacklistQueueHandler extends BaseQueue {
    public function process(): void {
        parent::baseProcess(\Utils\Constants::get('BLACKLIST_QUEUE_ACTION_TYPE'));
    }

    protected function processItem(array $item): void {
        $fraud = true;

        $dataController = new \Controllers\Admin\User\Data();
        $items = $dataController->setFraudFlag(
            $item['event_account'],
            $fraud,
            $item['key'],
        );

        $model = new \Models\User();
        $username = $model->getUser($item['event_account'], $item['key'])['userid'] ?? '';

        $msg = \Utils\SystemMessages::syslogLine(10, 5, 'BlacklistQueue', 'blacklisted userid=' . $username);
        \Base::instance()->write(\Base::instance()->LOGS . 'blacklist.log', $msg . PHP_EOL, true);

        $model = new \Models\ApiKeys();
        $model->getKeyById($item['key']);

        if (!$model->skip_blacklist_sync && $model->token) {
            $user = new \Models\User();
            $userEmail = $user->getUser($item['event_account'], $item['key'])['email'] ?? null;

            if ($userEmail !== null) {
                $hashes = \Utils\Cron::getHashes($items, $userEmail);
                $errorMessage = \Utils\Cron::sendBlacklistReportPostRequest($hashes, $model->token);
                if (strlen($errorMessage) > 0) {
                    // TODO: log error into database?
                    $this->addLog('Enrichment API cURL ' . $errorMessage);
                    $this->addLog('Enrichment API cURL logged to database.');
                }
            }
        }
    }
}
