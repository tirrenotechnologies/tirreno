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

class LogbookRotation extends AbstractCron {
    public function rotateRequests(): void {
        $this->log('Start logbook rotation.');

        $model = new \Models\ApiKeys();
        $keys = $model->getAllApiKeyIds();
        // rotate events for unauthorized requests
        $keys[] = ['id' => null];

        $model = new \Models\Logbook();
        $cnt = 0;
        foreach ($keys as $key) {
            $cnt += $model->rotateRequests($key['id']);
        }

        $this->log(sprintf('Deleted %s events for %s keys in logbook.', $cnt, count($keys)));
    }
}
