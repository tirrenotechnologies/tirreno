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

trait Emails {
    private function calculateEmailReputation(array &$records, string $fieldName = 'reputation'): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $r = $records[$i];
            $reputation = 'none';

            if ($r['data_breach'] !== null && $r['data_breach'] !== null) {
                $reputationLevel = (int) ($r['data_breach']) + (int) (!$r['blockemails']);
                $reputation = match ($reputationLevel) {
                    2       => 'high',
                    1       => 'medium',
                    0       => 'low',
                    default => 'none',
                };
            }

            /*if (!$r['profiles'] && !$r['data_breach'] && $r['blockemails']) {
                $reputation = 'low';
            } elseif (!$r['profiles'] && $r['data_breach'] && !$reputation) {
                $reputation = 'medium';
            } elseif ($r['profiles'] && !$r['data_breach'] && !$reputation) {
                $reputation = 'medium';
            } elseif ($r['profiles'] && $r['data_breach'] && !$reputation) {
                $reputation = 'high';
            } else {
                $reputation = 'none';
            }*/

            $r[$fieldName] = $reputation;

            $records[$i] = $r;
        }
    }

    private function calculateEmailReputationForContext(array &$records): void {
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $r = $records[$i];

            //$r['profiles'] = $r['ee_profiles'] ?? 0;
            $r['data_breach'] = $r['ee_data_breach'] ?? false;
            $r['blockemails'] = $r['ee_blockemails'] ?? false;
            //$r['disposable_domains'] = $r['ed_disposable_domains'] ?? false;

            $records[$i] = $r;
        }

        $fieldName = 'ee_reputation';
        $this->calculateEmailReputation($records, $fieldName);

        for ($i = 0; $i < $iters; ++$i) {
            $r = $records[$i];

            //unset($r['profiles']);
            unset($r['data_breach']);
            unset($r['blockemails']);
            //unset($r['disposable_domains']);

            $records[$i] = $r;
        }
    }
}
