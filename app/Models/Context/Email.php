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

class Email extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $records = $this->getDetails($accountIds, $apiKey);
        $recordsByAccount = $this->groupRecordsByAccount($records);

        foreach ($recordsByAccount as $key => $value) {
            $recordsByAccount[$key] = [
                'ee_email'              => $this->getUniqueArray(array_column($value, 'ee_email')),
                'ee_earliest_breach'    => $this->getUniqueArray(array_column($value, 'ee_earliest_breach')),
            ];
        }

        return $recordsByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_email.account_id         AS accountid,
                event_email.email              AS ee_email,
                event_email.earliest_breach    AS ee_earliest_breach
            FROM
                event_email

            WHERE
                event_email.key = :api_key
                AND event_email.checked = 'True'
                AND event_email.account_id IN ({$placeHolders})"
        );

        return $this->execQuery($query, $params);
    }
}
