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

class Enrichment {
    public static function calculateIpType(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            $type = null;

            if ($record['fraud_detected'] && !$type) {
                $type = 'Blacklisted';
            }
            if ($record['blocklist'] && !$type) {
                $type = 'Spam list';
            }
            if ($record['country_id'] === 0 && $record['checked'] && !$type) {
                $type = 'Localhost';
            }
            if ($record['tor'] && !$type) {
                $type = 'TOR';
            }
            if ($record['starlink'] && !$type) {
                $type = 'Starlink';
            }
            if ($record['relay'] && !$type) {
                $type = 'AppleRelay';
            }
            if ($record['vpn'] && !$type) {
                $type = 'VPN';
            }
            if ($record['data_center'] && !$type) {
                $type = 'Datacenter';
            }
            if (!$record['checked']) {
                $type = 'Unknown';
            }
            if (!$type) {
                $type = 'Residential';
            }

            unset($record['tor']);
            unset($record['starlink']);
            unset($record['relay']);
            unset($record['vpn']);
            unset($record['data_center']);

            $record['ip_type'] = $type;

            $records[$i] = $record;
        }
    }

    public static function calculateEmailReputation(array &$records, string $fieldName = 'reputation'): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];
            $reputation = 'none';

            if ($record['data_breach'] !== null && $record['data_breach'] !== null) {
                $reputationLevel = \Utils\Conversion::intVal($record['data_breach'], 0) + \Utils\Conversion::intVal(!$record['blockemails'], 0);
                $reputation = match ($reputationLevel) {
                    2       => 'high',
                    1       => 'medium',
                    0       => 'low',
                    default => 'none',
                };
            }

            /*if (!$record['profiles'] && !$record['data_breach'] && $record['blockemails']) {
                $reputation = 'low';
            } elseif (!$record['profiles'] && $record['data_breach'] && !$reputation) {
                $reputation = 'medium';
            } elseif ($record['profiles'] && !$record['data_breach'] && !$reputation) {
                $reputation = 'medium';
            } elseif ($record['profiles'] && $record['data_breach'] && !$reputation) {
                $reputation = 'high';
            } else {
                $reputation = 'none';
            }*/

            $record[$fieldName] = $reputation;

            $records[$i] = $record;
        }
    }

    public static function calculateEmailReputationForContext(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            //$record['profiles'] = $record['ee_profiles'] ?? 0;
            $record['data_breach'] = $record['ee_data_breach'] ?? false;
            $record['blockemails'] = $record['ee_blockemails'] ?? false;
            //$record['disposable_domains'] = $record['ed_disposable_domains'] ?? false;

            $records[$i] = $record;
        }

        $fieldName = 'ee_reputation';
        self::calculateEmailReputation($records, $fieldName);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            //unset($record['profiles']);
            unset($record['data_breach']);
            unset($record['blockemails']);
            //unset($record['disposable_domains']);

            $records[$i] = $record;
        }
    }

    public static function applyDeviceParams(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            $device = $record['device'] ?? 'unknown';
            $browserName = $record['browser_name'] ?? '';
            $browserVersion = $record['browser_version'] ?? '';
            $osName = $record['os_name'] ?? '';
            $osVersion = $record['os_version'] ?? '';

            //Display 'Bot' label instead of his full name
            $record['os_name'] = $device === 'bot' ? 'Bot' : $osName;

            $record['os'] = sprintf('%s %s', $osName, $osVersion);
            $record['browser'] = sprintf('%s %s', $browserName, $browserVersion);
            $record['device_name'] = $device;

            $records[$i] = $record;
        }
    }
}
