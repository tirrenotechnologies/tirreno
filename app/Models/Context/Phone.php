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

class Phone extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $records = $this->getDetails($accountIds, $apiKey);
        $recordsByAccount = $this->groupRecordsByAccount($records);

        foreach ($recordsByAccount as $key => $value) {
            $recordsByAccount[$key] = [
                //'ep_calling_country_code'   => $this->getUniqueArray(array_column($value, 'ep_calling_country_code')),
                //'ep_carrier_name'           => $this->getUniqueArray(array_column($value, 'ep_carrier_name')),
                //'ep_checked'                => $this->getUniqueArray(array_column($value, 'ep_checked')),
                //'ep_country_code'           => $this->getUniqueArray(array_column($value, 'ep_country_code')),
                //'ep_created'                => $this->getUniqueArray(array_column($value, 'ep_created')),
                //'ep_lastseen'               => $this->getUniqueArray(array_column($value, 'ep_lastseen')),
                //'ep_mobile_country_code'    => $this->getUniqueArray(array_column($value, 'ep_mobile_country_code')),
                //'ep_mobile_network_code'    => $this->getUniqueArray(array_column($value, 'ep_mobile_network_code')),
                //'ep_national_format'        => $this->getUniqueArray(array_column($value, 'ep_national_format')),
                'ep_phone_number'           => $this->getUniqueArray(array_column($value, 'ep_phone_number')),
                'ep_shared'                 => $this->getUniqueArray(array_column($value, 'ep_shared')),
                'ep_type'                   => $this->getUniqueArray(array_column($value, 'ep_type')),
                //'ep_invalid'                => $this->getUniqueArray(array_column($value, 'ep_invalid')),
                //'ep_validation_errors'      => $this->getUniqueArray(array_column($value, 'ep_validation_errors')),
                //'ep_alert_list'             => $this->getUniqueArray(array_column($value, 'ep_alert_list')),
            ];
        }

        return $recordsByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_phone.account_id           AS accountid,

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
                event_phone.key = :api_key
                AND event_phone.account_id IN ({$placeHolders})"
        );

        return $this->execQuery($query, $params);
    }
}
