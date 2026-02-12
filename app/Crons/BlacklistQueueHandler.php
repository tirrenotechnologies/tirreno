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
        parent::baseProcess(\Tirreno\Utils\Constants::get()->BLACKLIST_QUEUE_ACTION_TYPE);
    }

    protected function processItem(array $item): void {
        $f3 = \Base::instance();
        $fraud = true;

        $dataController = new \Tirreno\Controllers\Admin\User\Data();
        $items = $dataController->setFraudFlag(
            $item['event_account'],
            $fraud,
            $item['key'],
        );

        $model = new \Tirreno\Models\User();
        $username = $model->getUserById($item['event_account'], $item['key'])['userid'] ?? '';

        $msg = \Tirreno\Utils\SystemMessages::syslogLine(10, 5, 'BlacklistQueue', 'blacklisted userid=' . $username);
        $f3->write($f3->get('LOGS') . 'blacklist.log', $msg . PHP_EOL, true);

        $key = \Tirreno\Entities\ApiKey::getById($item['key']);

        if (!$key->skipBlacklistSync && $key->token) {
            $user = new \Tirreno\Models\User();
            $userEmail = $user->getUserById($item['event_account'], $item['key'])['email'] ?? null;

            if ($userEmail !== null) {
                $hashes = \Tirreno\Utils\Cron::getHashes($items, $userEmail);
                $errorMessage = \Tirreno\Utils\Cron::sendBlacklistReportPostRequest($hashes, $key->token);
                if (strlen($errorMessage) > 0) {
                    // TODO: log error into database?
                    $this->addLog('Enrichment API cURL ' . $errorMessage);
                    $this->addLog('Enrichment API cURL logged to database.');
                }
            }
        }
    }
}
