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
    'manualCheck_page_title' => 'Manual check',

    'manualCheck_form_title' => 'Manual check',
    'manualCheck_form_field_type_label' => 'Type',
    'manualCheck_form_types' => [
        'ip' => 'IP',
        'email' => 'Email',
        'domain' => 'Domain',
        'phone' => 'Phone',
    ],
    'manualCheck_form_field_search_query_label' => 'Search query',
    'manualCheck_form_button_search' => 'Search',

    'manualCheck_result_title' => '%s result',

    'manualCheck_key_overwrites' => [
        'ip' => 'IP',
        'email' => 'Email',
        'domain' => 'Domain',
        'phone' => 'Phone',
        'geo_ip' => 'Geo IP',
        'geo_html' => 'Geo HTML',
        'iso_country_code' => 'ISO country code',
        'asn' => 'ASN',
        'tor' => 'TOR',
        'vpn' => 'VPN',
        'mx_record' => 'MX record',
        'domains_count' => 'Domains hosting',
        'cidr' => 'CIDR',
    ],

    'manualCheck_history_title' => 'History',
];
