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

trait TimeZones {
    protected function translateTimeZone(array &$row, array $attributes = ['time', 'lastseen'], bool $useMilliseconds = false): void {
        foreach ($attributes as $attribute) {
            if (isset($row[$attribute]) && $row[$attribute] !== null) {
                \Utils\TimeZones::localizeForActiveOperator($row[$attribute], $useMilliseconds);
            }
        }
    }

    protected function translateTimeZones(array &$rows, array $attributes = ['time', 'lastseen'], bool $useMilliseconds = false): void {
        $rows = array_map(function ($row) use ($attributes, $useMilliseconds) {
            $this->translateTimeZone($row, $attributes, $useMilliseconds);

            return $row;
        }, $rows);
    }
}
