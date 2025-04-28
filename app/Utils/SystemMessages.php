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

namespace Utils;

class SystemMessages {
    public static function get(int $apiKey): array {
        $f3 = \Base::instance();

        $messages = [
            self::getNoEventsMessage($apiKey),
            self::getOveruseMessage($apiKey),
        ];
        // show no-crons warning only if events there are no valid incoming events
        if (!array_filter($messages)) {
            $messages[] = self::getInactiveCronMessage($apiKey);
        }
        $messages[] = self::getCustomErrorMessage($apiKey);
        $msg = [];

        for ($i = 0; $i < count($messages); ++$i) {
            $m = $messages[$i];
            if ($m !== null) {
                if ($m['id'] !== \Utils\ErrorCodes::CUSTOM_ERROR_FROM_DSHB_MESSAGES) {
                    $code = sprintf('error_%s', $m['id']);
                    $text = $f3->get($code);

                    $time = gmdate('Y-m-d H:i:s');
                    \Utils\TimeZones::localizeForActiveOperator($time);

                    $m['text'] = $text;
                    $m['created_at'] = $time;
                    $m['class'] = 'is-warning';
                }

                $msg[] = $m;
            }
        }

        return $msg;
    }

    private static function getNoEventsMessage(int $apiKey): ?array {
        $f3 = \Base::instance();
        $currentOperator = $f3->get('CURRENT_USER');

        $takeFromCache = self::canTakeLastEventTimeFromCache($currentOperator);

        $lastEventTime = $currentOperator->last_event_time;

        if (!$takeFromCache) {
            $model = new \Models\Event();
            $event = $model->getLastEvent($apiKey);

            if (!count($event)) {
                return ['id' => \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_YET];
            }

            $lastEventTime = $event[0]['time'];

            $data = [
                'id' => $currentOperator->id,
                'last_event_time' => $lastEventTime,
            ];

            $model = new \Models\Operator();
            $model->updateLastEventTime($data);
        }

        $currentTime = gmdate('Y-m-d H:i:s');

        $dt1 = new \DateTime($currentTime);
        $dt2 = new \DateTime($lastEventTime);
        $diff = $dt1->getTimestamp() - $dt2->getTimestamp();

        $noEventsThreshold = $f3->get('NO_EVENTS_TIME');
        $noEventsLast24Hours = $diff > $noEventsThreshold;

        if ($noEventsLast24Hours) {
            return ['id' => \Utils\ErrorCodes::THERE_ARE_NO_EVENTS_LAST_24_HOURS];
        }

        return null;
    }

    private static function getOveruseMessage(int $apiKey): ?array {
        $model = new \Models\ApiKeys();
        $model->getKeyById($apiKey);

        if ($model->last_call_reached === false) {
            return ['id' => \Utils\ErrorCodes::ENRICHMENT_API_KEY_OVERUSE];
        }

        return null;
    }

    private static function getInactiveCronMessage(int $apiKey): ?array {
        $cursorModel = new \Models\Queue\QueueNewEventsCursor();
        $eventModel = new \Models\Event();

        if ($cursorModel->getCursor() === 0 && count($eventModel->getLastEvent($apiKey))) {
            return ['id' => \Utils\ErrorCodes::CRON_JOB_MAY_BE_OFF];
        }

        return null;
    }

    //TODO: think about custom function which receives three params: date1, date2 and diff.
    private static function canTakeLastEventTimeFromCache(\Models\Operator $currentOperator): bool {
        $f3 = \Base::instance();

        $diff = PHP_INT_MAX;
        $currentTime = gmdate('Y-m-d H:i:s');
        $updatedAt = $currentOperator->last_event_time;

        if ($updatedAt) {
            $dt1 = new \DateTime($currentTime);
            $dt2 = new \DateTime($updatedAt);

            $diff = $dt1->getTimestamp() - $dt2->getTimestamp();
        }

        $cacheTime = $f3->get('LAST_EVENT_CACHE_TIME');

        return $cacheTime > $diff;
    }

    private static function getCustomErrorMessage(): ?array {
        $message = null;
        $model = new \Models\Message();

        $data = $model->getMessage();

        if ($data) {
            $message = [
                'id' => \Utils\ErrorCodes::CUSTOM_ERROR_FROM_DSHB_MESSAGES,
                'text' => $data->text,
                'created_at' => $data->created_at,
            ];
        }

        return $message;
    }
}
