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

trait Devices {
    public function applyDeviceParams(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $r = $records[$i];

            $device = $r['device'] ?? 'unknown';
            $browserName = $r['browser_name'] ?? '';
            $browserVersion = $r['browser_version'] ?? '';
            $osName = $r['os_name'] ?? '';
            $osVersion = $r['os_version'] ?? '';

            //Display 'Bot' label instead of his full name
            $r['os_name'] = $device === 'bot' ? 'Bot' : $osName;

            $r['os'] = sprintf('%s %s', $osName, $osVersion);
            $r['browser'] = sprintf('%s %s', $browserName, $browserVersion);
            $r['device_name'] = $device;

            $records[$i] = $r;
        }
    }
}
