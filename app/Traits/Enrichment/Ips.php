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

namespace Traits\Enrichment;

trait Ips {
    private function calculateIpType(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $r = $records[$i];

            $type = null;

            if ($r['fraud_detected'] && !$type) {
                $type = 'Blacklisted';
            }
            if ($r['blocklist'] && !$type) {
                $type = 'Spam list';
            }
            if ($r['country_id'] === 0 && $r['checked'] && !$type) {
                $type = 'Localhost';
            }
            if ($r['tor'] && !$type) {
                $type = 'TOR';
            }
            if ($r['starlink'] && !$type) {
                $type = 'Starlink';
            }
            if ($r['relay'] && !$type) {
                $type = 'AppleRelay';
            }
            if ($r['vpn'] && !$type) {
                $type = 'VPN';
            }
            if ($r['data_center'] && !$type) {
                $type = 'Datacenter';
            }
            if (!$r['checked']) {
                $type = 'Unknown';
            }
            if (!$type) {
                $type = 'Residential';
            }

            unset($r['tor']);
            unset($r['starlink']);
            unset($r['relay']);
            unset($r['vpn']);
            unset($r['data_center']);

            $r['ip_type'] = $type;

            $records[$i] = $r;
        }
    }
}
