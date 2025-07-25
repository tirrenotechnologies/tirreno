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

class Domain extends Base {
    public function getContext(array $accountIds, int $apiKey): array {
        $records = $this->getDetails($accountIds, $apiKey);
        $recordsByAccount = $this->groupRecordsByAccount($records);

        foreach ($recordsByAccount as $key => $value) {
            $recordsByAccount[$key] = [
                'ed_domain'                 => $this->getUniqueArray(array_column($value, 'ed_domain')),
                'ed_blockdomains'           => $this->getUniqueArray(array_column($value, 'ed_blockdomains')),
                'ed_disposable_domains'     => $this->getUniqueArray(array_column($value, 'ed_disposable_domains')),
                'ed_free_email_provider'    => $this->getUniqueArray(array_column($value, 'ed_free_email_provider')),
                'ed_creation_date'          => $this->getUniqueArray(array_column($value, 'ed_creation_date')),
                'ed_disabled'               => $this->getUniqueArray(array_column($value, 'ed_disabled')),
                'ed_mx_record'              => $this->getUniqueArray(array_column($value, 'ed_mx_record')),
            ];
        }

        return $recordsByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_email.account_id             AS accountid,
                event_domain.domain                AS ed_domain,
                event_domain.blockdomains          AS ed_blockdomains,
                event_domain.disposable_domains    AS ed_disposable_domains,
                event_domain.free_email_provider   AS ed_free_email_provider,
                event_domain.creation_date         AS ed_creation_date,
                event_domain.disabled              AS ed_disabled,
                event_domain.mx_record             AS ed_mx_record

            FROM
                event_domain

            INNER JOIN event_email
            ON event_domain.id = event_email.domain

            WHERE
                event_email.key = :api_key
                AND event_email.account_id IN ({$placeHolders})"
        );

        return $this->execQuery($query, $params);
    }
}
