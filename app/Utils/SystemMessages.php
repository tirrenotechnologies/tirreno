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

class SystemMessages {
    public static function get(int $apiKey): array {
        $messages = \Tirreno\Utils\Routes::callExtra('SYSTEM_MESSAGES') ?? [];

        // get last event timestamp from event_account.lastseen to avoid long reindexing on login
        $lastLogbook = (new \Tirreno\Models\Logbook())->getLastSucceededEvent($apiKey);

        $messages[] = self::getNoEventsMessage($lastLogbook);
        $messages[] = self::getOveruseMessage($apiKey);

        // show no-crons warning only if events there are no valid incoming events
        if (!array_filter($messages)) {
            $messages[] = self::getInactiveCronMessage($lastLogbook, $apiKey);
        }
        $messages[] = self::getCustomErrorMessage();
        $msg = [];

        $iters = count($messages);

        for ($i = 0; $i < $iters; ++$i) {
            $message = $messages[$i];
            if ($message !== null) {
                if ($message['id'] !== \Tirreno\Utils\ErrorCodes::CUSTOM_ERROR_FROM_DSHB_MESSAGES) {
                    $code = sprintf('error_%s', $message['id']);
                    $text = \Base::instance()->get($code);

                    $time = gmdate('Y-m-d H:i:s');
                    \Tirreno\Utils\Timezones::localizeForActiveOperator($time);

                    $message['text'] = $text;
                    $message['created_at'] = $time;
                    $message['class'] = 'is-warning';
                }

                $msg[] = $message;
            }
        }

        return $msg;
    }

    public static function syslogLine(int $facility, int $severity, string $app, string $msg): string {
        // facility 0 -> 23
        // severity 0 -> 7
        $pri        = $facility * 8 + $severity;
        $timestamp  = date('M j H:i:s');
        $host       = 'tirreno';
        $pid        = getmypid();
        $msg        = str_replace(["\r","\n"], ' ', $msg);

        return sprintf('<%d>%s %s %s[%d]: %s', $pri, $timestamp, $host, $app, $pid, $msg);
    }

    private static function getNoEventsMessage(array $lastLogbook): ?array {
        $currentOperator = \Tirreno\Utils\Routes::getCurrentRequestOperator();
        $takeFromCache = self::canTakeLastEventTimeFromCache($currentOperator);
        $lastEventTime = $currentOperator->lastEventTime;

        $interval   = \Base::instance()->get('NO_EVENTS_TIME');
        $inInterval = \Tirreno\Utils\DateRange::inIntervalTillNow($lastEventTime, $interval);

        if (!$takeFromCache || !$inInterval) {
            if (!count($lastLogbook)) {
                return ['id' => \Tirreno\Utils\ErrorCodes::THERE_ARE_NO_EVENTS_YET];
            }

            $lastEventTime = $lastLogbook['lastseen'];

            $model = new \Tirreno\Models\Operator();
            $model->updateLastEventTime($lastEventTime, $currentOperator->id);

            $inInterval = \Tirreno\Utils\DateRange::inIntervalTillNow($lastEventTime, $interval);
        }

        if (!$inInterval) {
            return ['id' => \Tirreno\Utils\ErrorCodes::THERE_ARE_NO_EVENTS_LAST_DAY];
        }

        return null;
    }

    private static function getOveruseMessage(int $apiKey): ?array {
        $key = \Tirreno\Entities\ApiKey::getById($apiKey);

        if ($key->lastCallReached === false) {
            return ['id' => \Tirreno\Utils\ErrorCodes::ENRICHMENT_API_KEY_OVERUSE];
        }

        return null;
    }

    private static function getInactiveCronMessage(array $lastLogbook, int $apiKey): ?array {
        $cursorModel = new \Tirreno\Models\Cursor();

        if ($cursorModel->getCursor() === 0 && count($lastLogbook)) {
            return ['id' => \Tirreno\Utils\ErrorCodes::CRON_JOB_MAY_BE_OFF];
        }

        return null;
    }

    //TODO: think about custom function which receives three params: date1, date2 and diff.
    private static function canTakeLastEventTimeFromCache(\Tirreno\Entities\Operator $operator): bool {
        $interval = \Base::instance()->get('LAST_EVENT_CACHE_TIME');

        return !!\Tirreno\Utils\DateRange::inIntervalTillNow($operator->lastEventTime, $interval);
    }

    // TODO: get message by api key?
    private static function getCustomErrorMessage(): ?array {
        $message = null;
        $model = new \Tirreno\Models\Message();

        $data = $model->getLastMessage();

        if ($data) {
            $message = [
                'id'            => \Tirreno\Utils\ErrorCodes::CUSTOM_ERROR_FROM_DSHB_MESSAGES,
                'text'          => $data['text'],
                'created_at'    => $data['created_at'],
            ];
        }

        return $message;
    }
}
