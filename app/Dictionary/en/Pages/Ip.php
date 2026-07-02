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

return [
    'ip_page_title' => 'IP',
    'ip_breadcrumb_title' => 'IP',
    'ip_ips_table_by_cidr_title' => 'Nearby IP addresses',

    'ip_counters_country' => 'Country',
    'ip_counters_country_tooltip' => 'A country geolocated by the IP address.',
    'ip_counters_asn' => 'ASN',
    'ip_counters_blocklist' => 'Spam list',
    'ip_counters_blocklist_tooltip' => 'Someone may have utilized this IP address to exhibit unwanted activity before at other web services.',
    'ip_counters_blacklist' => 'Blacklisted',
    'ip_counters_blacklist_tooltip' => 'Whether this IP address is in the blacklist.',
    'ip_counters_datacenter' => 'Datacenter',
    'ip_counters_datacenter_tooltip' => 'This IP address belongs to ISP datacenter, which highly suggests the use of a VPN, script, or privacy software.',
    'ip_counters_vpn' => 'VPN',
    'ip_counters_vpn_tooltip' => 'This IP address is used to hide entity\'s real location or to bypass regional blocking.',
    'ip_counters_tor' => 'TOR',
    'ip_counters_tor_tooltip' => 'IP address is assigned to The Onion Router network. Very few people use TOR, mainly used for anonymization and accessing censored resources.',
    'ip_counters_apple_relay' => 'Apple Relay',
    'ip_counters_apple_relay_tooltip' => 'IP address belongs to iCloud Private Relay, part of an iCloud+ subscription.',
];
