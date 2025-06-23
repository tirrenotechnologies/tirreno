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

class Rules {
    public static function checkPhoneCountryMatchIp(array $params): bool {
        if (is_null($params['lp_country_code']) || $params['lp_country_code'] === 0) {
            return true;
        }

        return in_array($params['lp_country_code'], $params['eip_country_id']);
    }

    public static function eventDeviceIsNew(array $params, int $idx): bool {
        $deviceCreated = new \DateTime($params['event_device_created'][$idx]);
        $deviceLastseen = new \DateTime($params['event_device_lastseen'][$idx]);
        $interval = $deviceCreated->diff($deviceLastseen);

        return abs($interval->days * 24 * 60 + $interval->h * 60 + $interval->i) < \Utils\Constants::get('RULE_NEW_DEVICE_MAX_AGE_IN_MINUTES');
    }

    public static function countryIsNewByIpId(array $params, int $ipId): bool {
        $filtered = array_filter($params['eip_country_id'], function ($value) {
            return $value !== null;
        });
        $countryCounts = array_count_values($filtered);
        $ipIdx = array_search($ipId, $params['eip_ip_id']);
        $eventIpCountryId = $params['eip_country_id'][$ipIdx];
        $count = $countryCounts[$eventIpCountryId] ?? 0;

        return $count === 1;
    }

    public static function cidrIsNewByIpId(array $params, int $ipId): bool {
        $filtered = array_filter($params['eip_cidr'], function ($value) {
            return $value !== null;
        });
        $cidrCounts = array_count_values($filtered);
        $ipIdx = array_search($ipId, $params['eip_ip_id']);
        $eventIpCidr = $params['eip_cidr'][$ipIdx];
        $count = $cidrCounts[$eventIpCidr] ?? 0;

        return $count === 1;
    }
}
