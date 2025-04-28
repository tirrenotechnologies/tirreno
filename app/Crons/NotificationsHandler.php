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

declare(strict_types=1);

namespace Crons;

class NotificationsHandler extends AbstractCron {
    private const NOTIFICATION_WINDOW_HOUR_START = 9;
    private const NOTIFICATION_WINDOW_HOUR_END = 17;

    private \Models\NotificationPreferences $notificationPreferencesModel;

    public function __construct() {
        parent::__construct();

        $this->notificationPreferencesModel = new \Models\NotificationPreferences();
    }

    public function prepareNotifications(): void {
        $timezonesInWindow = $this->getTimeZonesInWindow(self::NOTIFICATION_WINDOW_HOUR_START, self::NOTIFICATION_WINDOW_HOUR_END);

        $operatorsToNotify = $this->notificationPreferencesModel->listOperatorsEligableForUnreviewedItemsReminder($timezonesInWindow);

        foreach ($operatorsToNotify as $operator) {
            try {
                $this->sendUnreviewedItemsReminderEmail($operator['firstname'] ?? '', $operator['email'], $operator['review_queue_cnt']);
            } catch (\Throwable $e) {
                $this->log(sprintf('Notification handler error %s.', $e->getMessage()));
            }
        }

        $count = \count($operatorsToNotify);

        if ($count > 0) {
            $this->notificationPreferencesModel->updateLastUnreviewedItemsReminder(\array_column($operatorsToNotify, 'id'));
        }

        $this->log(sprintf('Sent %s unreviewed items reminder notifications.', $count));
    }

    /**
     * @return string[] Time zones currently in the notification window
     */
    private function getTimeZonesInWindow(int $startHour, int $endHour): array {
        $timezones = \DateTimeZone::listIdentifiers();

        return \array_filter($timezones, function ($timezone) use ($startHour, $endHour) {
            $date = new \DateTime('now', new \DateTimeZone($timezone));
            $hour = (int) $date->format('H');

            return $hour >= $startHour && $hour < $endHour;
        });
    }

    private function sendUnreviewedItemsReminderEmail(string $recipientFirstName, string $recipientEmail, int $reviewCount): void {
        $audit = \Audit::instance();
        if (!$audit->email($recipientEmail, true)) {
            $this->log(sprintf('Username `%s` is not email; review count is %s', $recipientEmail, $reviewCount));

            return;
        }

        $subject = $this->f3->get('UnreviewedItemsReminder_email_subject');
        $subject = sprintf($subject, $reviewCount);

        $message = $this->f3->get('UnreviewedItemsReminder_email_body');
        $url = \Utils\Variables::getSiteWithProtocol();
        $message = sprintf($message, $recipientFirstName, $recipientEmail, $reviewCount, $url);

        \Utils\Mailer::send($recipientFirstName, $recipientEmail, $subject, $message);
    }
}
