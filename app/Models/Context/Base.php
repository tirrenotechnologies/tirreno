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

namespace Models\Context;

abstract class Base extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event';

    protected function getRequestParams(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getArrayPlaceholders($accountIds);
        $params[':api_key'] = $apiKey;

        return [$params, $placeHolders];
    }

    protected function groupRecordsByAccount(array $records): array {
        $recordsByAccount = [];
        $iters = count($records);

        for ($i = 0; $i < $iters; ++$i) {
            $item = $records[$i];
            $accountId = $item['accountid'];

            if (!isset($recordsByAccount[$accountId])) {
                $recordsByAccount[$accountId] = [];
            }

            $recordsByAccount[$accountId][] = $item;
        }

        return $recordsByAccount;
    }

    protected function getUniqueArray(array $array): array {
        return array_values(array_unique($array));
    }
}
