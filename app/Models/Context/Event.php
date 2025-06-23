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

namespace Models\Context;

class Event extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $records = $this->getEventDetails($accountIds, $apiKey);
        $recordsByAccount = $this->groupRecordsByAccount($records);

        foreach ($recordsByAccount as $key => $value) {
            $recordsByAccount[$key] = [
                'event_ip'              => array_column($value, 'event_ip'),
                'event_url_string'      => array_column($value, 'event_url_string'),
                'event_empty_referer'   => array_column($value, 'event_empty_referer'),
                'event_device'          => array_column($value, 'event_device'),
                'event_type'            => array_column($value, 'event_type'),
                'event_http_code'       => array_column($value, 'event_http_code'),
                'event_device_created'  => array_column($value, 'event_device_created'),
                'event_device_lastseen' => array_column($value, 'event_device_lastseen'),
                'event_http_method'     => array_column($value, 'event_http_method'),
            ];
        }

        return $recordsByAccount;
    }

    private function getEventDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);
        $contextLimit = \Utils\Constants::get('RULE_EVENT_CONTEXT_LIMIT');

        $query = (
            "WITH ranked_events AS (
                SELECT
                    event.account           AS accountid,
                    event.id                AS event_id,
                    event.ip                AS event_ip,
                    event_url.url           AS event_url_string,
                    event_referer.referer   AS event_referer_string,
                    event.device            AS event_device,
                    event.time              AS event_time,
                    event.type              AS event_type,
                    event.http_code         AS event_http_code,
                    event.http_method       AS event_http_method,
                    ROW_NUMBER() OVER (PARTITION BY event.account ORDER BY event.time DESC) AS rn
                FROM event
                LEFT JOIN event_url ON event_url.id = event.url
                LEFT JOIN event_referer ON event_referer.id = event.referer
                WHERE event.key = :api_key
                AND event.account IN ({$placeHolders})
            )
            SELECT
                accountid,
                event_ip,
                event_url_string,
                (event_referer_string IS NULL OR event_referer_string = '') AS event_empty_referer,
                event_device,
                ed.created AS event_device_created,
                ed.lastseen AS event_device_lastseen,
                event_type,
                event_http_code,
                event_http_method
            FROM ranked_events
            LEFT JOIN event_device AS ed
            ON ranked_events.event_device = ed.id
            WHERE rn <= {$contextLimit}
            ORDER BY event_time DESC;"
        );

        return $this->execQuery($query, $params);
    }
}
