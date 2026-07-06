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

namespace Tirreno\Utils;

class Cron {
    protected const NOTIFICATION_WINDOW_HOUR_START = 9;
    protected const NOTIFICATION_WINDOW_HOUR_END = 17;

    protected const RANGES = [
        ['min' => 0, 'max' => 59], // minute
        ['min' => 0, 'max' => 23], // hour
        ['min' => 1, 'max' => 31], // day of month
        ['min' => 1, 'max' => 12], // month
        ['min' => 0, 'max' => 6],  // day of week (0 = Sunday)
    ];
    protected const PATTERN = '/^(\*|\d+)(?:-(\d+))?(?:\/(\d+))?$/';

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

    public static function sendBlacklistReportPostRequest(array $hashes, string $enrichmentKey, int $apiKey): string {
        $postFields = [
            'data' => $hashes,
        ];

        $response = tirreno('utils')->network->sendApiRequest($postFields, '/global_alert_report', 'POST', $enrichmentKey, $apiKey);

        return $response->error ?? '';
    }

    public static function checkTimezone(string $timezone): bool {
        $hour = (new \DateTime('now', tirreno('utils')->timezones->getTimezone($timezone)))->format('H');
        $hour = tirreno('utils')->conversion->intValCheckEmpty($hour, 0);

        return $hour >= self::NOTIFICATION_WINDOW_HOUR_START && $hour < self::NOTIFICATION_WINDOW_HOUR_END;
    }

    public static function sendUnreviewedItemsReminderEmail(string $name, string $email, int $reviewCount): bool {
        $audit = \Audit::instance();
        if (!$audit->email($email, true)) {
            return false;
        }

        $subject = tirreno('storage')->get('UnreviewedItemsReminder_email_subject');
        $subject = sprintf($subject, $reviewCount);

        $message = tirreno('storage')->get('UnreviewedItemsReminder_email_body');
        $url = tirreno('utils')->variables->getHostWithProtocolAndBase();
        $message = sprintf($message, $name, $email, $reviewCount, $url);

        tirreno('utils')->mailer->send($name, $email, $subject, $message);

        return true;
    }

    public static function printLogs(array $logs): void {
        foreach ($logs as $log) {
            echo $log;
        }
    }

    public static function parseTimestamp(\DateTime $time): array {
        return [
            tirreno('utils')->conversion->intValCheckEmpty($time->format('i'), 0), // minute
            tirreno('utils')->conversion->intValCheckEmpty($time->format('H'), 0), // hour
            tirreno('utils')->conversion->intValCheckEmpty($time->format('d'), 1), // day of month
            tirreno('utils')->conversion->intValCheckEmpty($time->format('m'), 1), // month
            tirreno('utils')->conversion->intValCheckEmpty($time->format('w'), 0), // day of week
        ];
    }

    public static function parseExpression(string $expression): false|array {
        $parts = [];
        $expressionParts = preg_split('/\s+/', trim($expression), -1, PREG_SPLIT_NO_EMPTY);

        if (count($expressionParts) !== 5) {
            return false;
        }

        foreach ($expressionParts as $i => $field) {
            $values = [];
            // handle lists
            $fieldParts = explode(',', $field);

            foreach ($fieldParts as $part) {
                if (!preg_match(self::PATTERN, $part, $matches)) {
                    return false;
                }

                $start = $matches[1];
                $end = $matches[2] ?? null;
                $step = $matches[3] ?? 1;

                // Convert '*' to start and end values
                if ($start === '*') {
                    $start = self::RANGES[$i]['min'];
                    $end = self::RANGES[$i]['max'];
                } else {
                    $start = tirreno('utils')->conversion->intValCheckEmpty($start, 0);
                    $end = tirreno('utils')->conversion->intValCheckEmpty($end, $start);
                }
                $step = tirreno('utils')->conversion->intValCheckEmpty($step, 0);

                if ($start > $end || $start < self::RANGES[$i]['min'] || $end > self::RANGES[$i]['max'] || $step < 1) {
                    return false;
                }

                $range = range($start, $end, $step);
                $values = array_merge($values, $range);
            }

            $parts[$i] = array_unique($values);
            sort($parts[$i]);
        }

        return $parts;
    }
}
