<?php

/**
 * tirreno ~ open security analytics
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

namespace Utils;

class ApiResponseFormats {
    // email and two phone responses??
    public static function getErrorResponseFormat(): array {
        return [
            'value',    // str
            'type',     // str
            'error',    // str
        ];
    }

    public static function getDomainFoundResponseFormat(): array {
        return [
            'domain',               // str
            'blockdomains',         // bool
            'disposable_domains',   // bool
            'free_email_provider',  // bool
            'creation_date',        // date || null
            'expiration_date',      // date || null
            'return_code',          // int  || null
            'disabled',             // bool
            'closest_snapshot',     // date || null
            'mx_record',            // bool
            'ip',                   // IPvAnyAddress || null
            'geo_ip',               // str  || null
            'geo_html',             // str  || null
            'web_server',           // str  || null
            'hostname',             // str  || null
            'emails',               // str  || null
            'phone',                // str  || null
            'discovery_date',       // date
            'tranco_rank',          // int  || null
        ];
    }

    public static function getDomainNotFoundResponseFormat(): array {
        return [
            'domain',               // str
            'blockdomains',         // bool
            'disposable_domains',   // bool
            'free_email_provider',  // bool
            'creation_date',        // date || null
            'expiration_date',      // date || null
            'return_code',          // int  || null
            'disabled',             // bool
            'closest_snapshot',     // date || null
            'mx_record',            // bool
        ];
    }

    public static function getIpResponseFormat(): array {
        return [
            'ip',               // IPvAnyAddress
            'country',          // str
            'asn',              // int  || null
            'name',             // str  || null
            'hosting',          // bool
            'vpn',              // bool
            'tor',              // bool
            'relay',            // bool
            'starlink',         // bool
            'description',      // str  || null
            'blocklist',        // bool
            'domains_count',    // list[str]
            'cidr',             // IPvAnyNetwork
            'alert_list',       // bool || null
        ];
    }

    public static function getUaResponseFormat(): array {
        return [
            'ua',               // str
            'device',           // str || null
            'browser_name',     // str || null
            'browser_version',  // str || null
            'os_name',          // str || null
            'os_version',       // str || null
            'modified',         // bool
        ];
    }

    public static function matchResponse(array $arr, array $format): bool {
        $allKeysPresent = true;
        foreach ($format as $key) {
            if (!isset($arr[$key])) {
                $allKeysPresent = false;
                break;
            }
        }

        return $allKeysPresent;
    }
}
