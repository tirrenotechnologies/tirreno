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

class Device extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $record = $this->getDetails($accountIds, $apiKey);
        $recordByAccount = $this->groupRecordsByAccount($record);

        foreach ($recordByAccount as $key => $value) {
            $recordByAccount[$key] = [
                'eup_device'            => array_column($value, 'eup_device'),
                'eup_device_id'         => array_column($value, 'eup_device_id'),
                'eup_browser_name'      => array_column($value, 'eup_browser_name'),
                'eup_browser_version'   => array_column($value, 'eup_browser_version'),
                'eup_os_name'           => array_column($value, 'eup_os_name'),
                'eup_lang'              => array_column($value, 'eup_lang'),
                'eup_ua'                => array_column($value, 'eup_ua'),
                // 'eup_lastseen'       => array_column($value, 'eup_lastseen'),
                // 'eup_created'        => array_column($value, 'eup_created'),
            ];
        }

        return $recordByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_device.account_id         AS accountid,
                event_device.id                 AS eup_device_id,
                event_ua_parsed.device          AS eup_device,
                event_ua_parsed.browser_name    AS eup_browser_name,
                event_ua_parsed.browser_version AS eup_browser_version,
                event_ua_parsed.os_name         AS eup_os_name,
                event_ua_parsed.ua              AS eup_ua,
                -- event_device.lastseen           AS eup_lastseen,
                -- event_device.created            AS eup_created,
                event_device.lang               AS eup_lang

            FROM
                event_device

            INNER JOIN event_ua_parsed
            ON(event_device.user_agent=event_ua_parsed.id)

            WHERE
                event_device.key = :api_key
                AND event_ua_parsed.checked = true
                AND event_device.account_id IN ({$placeHolders})"
        );

        return $this->execQuery($query, $params);
    }
}
