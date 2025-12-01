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

class NotificationsHandler extends Base {
    public function process(): void {
        $model = new \Models\NotificationPreferences();

        $operators = $model->operatorsToNotify();

        $cnt = 0;
        $failed = 0;

        foreach ($operators as $operator) {
            if (\Utils\Cron::checkTimezone($operator['timezone'] ?? '')) {
                try {
                    $name   = $operator['firstname'] ?? '';
                    $email  = $operator['email'] ?? '';
                    $review = $operator['review_queue_cnt'] ?? 0;
                    if (!\Utils\Cron::sendUnreviewedItemsReminderEmail($name, $email, $review)) {
                        $this->addLog(sprintf('Username `%s` is not email; review count is %s', $email, $review));
                    }
                    $model->updateUnreviewedReminder($operator['id']);
                    $cnt++;
                } catch (\Throwable $e) {
                    $this->addLog(sprintf('Notification handler error %s.', $e->getMessage()));
                    $failed++;
                }
            }
        }

        $this->addLog(sprintf('Sent %s unreviewed items reminder notifications, failed %s.', $cnt, $failed));
    }
}
