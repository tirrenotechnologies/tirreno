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

namespace Utils;

class Cron {
    private const NOTIFICATION_WINDOW_HOUR_START = 9;
    private const NOTIFICATION_WINDOW_HOUR_END = 17;

    public static function getHashes(array $items, string $userEmail): array {
        $userHash = hash('sha256', $userEmail);

        return array_map(function ($item) use ($userHash) {
            return [
                'type'  => $item['type'],
                'value' => hash('sha256', $item['value']),
                'id'    => $userHash,
            ];
        }, $items);
    }

    public static function sendBlacklistReportPostRequest(array $hashes, string $enrichmentKey): string {
        $postFields = [
            'data' => $hashes,
        ];

        $response = \Utils\Network::sendApiRequest($postFields, '/global_alert_report', 'POST', $enrichmentKey);

        return $response['error'] ?? '';
    }

    public static function checkTimezone(string $timezone): bool {
        $hour = (new \DateTime('now', \Utils\TimeZones::getTimezone($timezone)))->format('H');
        $hour = \Utils\Conversion::intValCheckEmpty($hour, 0);

        return $hour >= self::NOTIFICATION_WINDOW_HOUR_START && $hour < self::NOTIFICATION_WINDOW_HOUR_END;
    }

    public static function sendUnreviewedItemsReminderEmail(string $name, string $email, int $reviewCount): bool {
        $audit = \Audit::instance();
        if (!$audit->email($email, true)) {
            return false;
        }

        $subject = \Base::instance()->get('UnreviewedItemsReminder_email_subject');
        $subject = sprintf($subject, $reviewCount);

        $message = \Base::instance()->get('UnreviewedItemsReminder_email_body');
        $url = \Utils\Variables::getHostWithProtocolAndBase();
        $message = sprintf($message, $name, $email, $reviewCount, $url);

        \Utils\Mailer::send($name, $email, $subject, $message);

        return true;
    }

    public static function printLogs(array $logs): void {
        foreach ($logs as $log) {
            echo $log;
        }
    }
}
