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

namespace Tirreno\Models\Query;

class Ips extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event_ip';
        $this->model = 'ips';

        $this->fields = [
            'event_ip.id'               => 'ip_id',
            'event_ip.ip'               => 'ip_ip',
            'event_ip.cidr'             => 'ip_cidr',
            'event_ip.data_center'      => 'ip_data_center',
            'event_ip.tor'              => 'ip_tor',
            'event_ip.vpn'              => 'ip_vpn',
            'event_ip.starlink'         => 'ip_starlink',
            'event_ip.blocklist'        => 'ip_blocklist',
            'event_ip.relay'            => 'ip_relay',
            'event_ip.checked'          => 'ip_checked',
            'event_ip.shared'           => 'ip_shared',
            'event_ip.fraud_detected'   => 'ip_fraud_detected',
            'event_ip.total_visit'      => 'ip_total_visit',
            'event_ip.lastseen'         => 'ip_lastseen',
            'event_ip.created'          => 'ip_created',
            'event_ip.updated'          => 'ip_updated',

            'event_isp.id'              => 'isp_id',
            'event_isp.asn'             => 'isp_asn',
            'event_isp.name'            => 'isp_name',
            'event_isp.description'     => 'isp_description',
            'event_isp.total_ip'        => 'isp_total_ip',
            'event_isp.total_visit'     => 'isp_total_visit',
            'event_isp.total_account'   => 'isp_total_account',
            'event_isp.lastseen'        => 'isp_lastseen',
            'event_isp.created'         => 'isp_created',
            'event_isp.updated'         => 'isp_updated',

            'countries.id'              => 'country_id',
            'countries.value'           => 'country_name',
            'countries.iso'             => 'country_iso',

            'event_country.id'              => 'country_data_id',
            'event_country.total_visit'     => 'country_total_visit',
            'event_country.total_ip'        => 'country_total_ip',
            'event_country.total_account'   => 'country_total_account',
            'event_country.lastseen'        => 'country_lastseen',
            'event_country.created'         => 'country_created',
            'event_country.updated'         => 'country_updated',
        ];

        $this->join = [
            'event_isp'     => 'LEFT JOIN event_isp ON event_isp.id = event_ip.isp',
            'countries'     => 'LEFT JOIN countries ON countries.id = event_ip.country',
            'event_country' => 'LEFT JOIN event_country ON event_country.country = countries.id AND event_country.key = event_ip.key',
        ];

        parent::__construct($key);
    }
}
