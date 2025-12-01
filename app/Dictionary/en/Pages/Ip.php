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

return [
    'AdminIp_page_title' => 'IP',
    'AdminIp_breadcrumb_title' => 'IP',
    'AdminIp_ips_table_by_cidr_title' => 'Nearby IP addresses',

    'AdminIp_counters_country' => 'Country',
    'AdminIp_counters_country_tooltip' => 'A country geolocated by the IP address.',
    'AdminIp_counters_asn' => 'ASN',
    'AdminIp_counters_blocklist' => 'Spam list',
    'AdminIp_counters_blocklist_tooltip' => 'Someone may have utilized this IP address to exhibit unwanted activity before at other web services.',
    'AdminIp_counters_blacklist' => 'Blacklisted',
    'AdminIp_counters_blacklist_tooltip' => 'Whether this IP address is in the blacklist.',
    'AdminIp_counters_datacenter' => 'Datacenter',
    'AdminIp_counters_datacenter_tooltip' => 'This IP address belongs to ISP datacenter, which highly suggests the use of a VPN, script, or privacy software.',
    'AdminIp_counters_vpn' => 'VPN',
    'AdminIp_counters_vpn_tooltip' => 'This IP address is used to hide user\'s real location or to bypass regional blocking.',
    'AdminIp_counters_tor' => 'TOR',
    'AdminIp_counters_tor_tooltip' => 'IP address is assigned to The Onion Router network. Very few people use TOR, mainly used for anonymization and accessing censored resources.',
    'AdminIp_counters_apple_relay' => 'Apple Relay',
    'AdminIp_counters_apple_relay_tooltip' => 'IP address belongs to iCloud Private Relay, part of an iCloud+ subscription.',
];
