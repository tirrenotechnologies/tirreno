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

class Phone extends Base {
    protected ?bool $uniqueValues = true;

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_phone.account_id           AS id,

                -- event_phone.calling_country_code AS ep_calling_country_code,
                -- event_phone.carrier_name         AS ep_carrier_name,
                -- event_phone.checked              AS ep_checked,
                -- event_phone.country_code         AS ep_country_code,
                -- event_phone.created              AS ep_created,
                -- event_phone.lastseen             AS ep_lastseen,
                -- event_phone.mobile_country_code  AS ep_mobile_country_code,
                -- event_phone.mobile_network_code  AS ep_mobile_network_code,
                -- event_phone.national_format      AS ep_national_format,
                event_phone.phone_number         AS ep_phone_number,
                event_phone.shared               AS ep_shared,
                event_phone.type                 AS ep_type
                -- event_phone.invalid              AS ep_invalid,
                -- event_phone.validation_errors    AS ep_validation_errors,
                -- event_phone.alert_list           AS ep_alert_list

            FROM
                event_phone

            WHERE
                event_phone.account_id IN ({$placeHolders}) AND
                event_phone.key = :api_key"
        );

        return $this->execQuery($query, $params);
    }
}
