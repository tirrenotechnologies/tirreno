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

namespace Crons;

class RetentionPolicyViolations extends AbstractCron {
    public function gatherViolations(): void {
        $this->log('Start retention policy violations.');

        $eventsModel = new \Models\Events();
        $retentionModel = new \Models\RetentionPolicies();

        $retentionKeys = $retentionModel->getRetentionKeys();
        $cnt = 0;

        foreach ($retentionKeys as $key) {
            // insuring clause
            if ($key['retention_policy'] > 0) {
                $cnt += $eventsModel->retentionDeletion($key['retention_policy'], $key['id']);
            }
        }

        $this->log(sprintf('Deleted %s events for %s operators due to retention policy violations.', $cnt, count($retentionKeys)));
    }
}
