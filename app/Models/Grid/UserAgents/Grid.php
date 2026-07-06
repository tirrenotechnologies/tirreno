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

namespace Tirreno\Models\Grid\UserAgents;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function getAll(int $apiKey): array {
        return $this->getGrid($apiKey);
    }

    protected function calculateCustomParams(array &$result): void {
        $result = tirreno('utils')->enrichment->applyDeviceParams($result);
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['lastseen'];

        $result = tirreno('utils')->timezones->translateTimezones($result, $fields);
    }
}
