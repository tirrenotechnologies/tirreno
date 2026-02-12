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

class Rules {
    public static function checkPhoneCountryMatchIp(array $params): ?bool {
        if (is_null($params['lp_country_code']) || $params['lp_country_code'] === 0) {
            return null;
        }

        return in_array($params['lp_country_code'], $params['eip_country_id']);
    }

    public static function eventDeviceIsNew(array $params, int $idx): bool {
        $deviceCreated = new \DateTime($params['event_device_created'][$idx]);
        $deviceLastseen = new \DateTime($params['event_device_lastseen'][$idx]);

        return abs($deviceLastseen->getTimestamp() - $deviceCreated->getTimestamp()) < \Tirreno\Utils\Constants::get()->RULE_NEW_DEVICE_MAX_AGE_IN_SECONDS;
    }

    public static function countryIsNewByIpId(array $params, int $ipId): bool {
        $countryId = null;
        if (array_key_exists($ipId, $params['eip_ip_id'])) {
            $countryId = $params['eip_ip_id'][$ipId]['country'] ?? null;
        }

        $count = null;
        if ($countryId !== null && array_key_exists($countryId, $params['eip_country_count'])) {
            $count = $params['eip_country_count'][$countryId];
        }

        return ($count === 1);
    }

    public static function cidrIsNewByIpId(array $params, int $ipId): bool {
        $cidr = null;
        if (array_key_exists($ipId, $params['eip_ip_id'])) {
            $cidr = $params['eip_ip_id'][$ipId]['cidr'] ?? null;
        }

        $count = null;
        if ($cidr !== null && array_key_exists($cidr, $params['eip_cidr_count'])) {
            $count = $params['eip_cidr_count'][$cidr];
        }

        return ($count === 1);
    }
}
