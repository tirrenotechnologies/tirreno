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

namespace Tirreno\Controllers\Services;

class Logbook extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        return tirreno('grids')->logbook->getAll($apiKey);
    }

    public function getChart(int $apiKey): array {
        return tirreno('charts')->logbook->getData($apiKey);
    }

    public function getLogbookDetails(int $id, int $apiKey): array {
        $result = tirreno('models')->logbook->getLogbookDetails($id, $apiKey);

        $tsColumns = ['started', 'ended'];
        $result = tirreno('utils')->timezones->localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }
}
