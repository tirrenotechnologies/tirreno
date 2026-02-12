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

namespace Tirreno\Models\Context;

class Device extends Base {
    protected ?bool $uniqueValues = false;

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_device.account_id         AS id,
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
                event_device.account_id IN ({$placeHolders}) AND
                event_ua_parsed.checked IS TRUE AND
                event_device.key = :api_key"
        );

        return $this->execQuery($query, $params);
    }
}
