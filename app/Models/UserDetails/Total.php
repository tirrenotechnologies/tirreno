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

namespace Tirreno\Models\UserDetails;

class Total extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event';

    public function getDetails(int $userId, int $apiKey): array {
        $params = [
            ':user_id'  => $userId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                event_account.id                AS accountid,
                event_account.total_ip          AS ips,
                event_account.total_country     AS countries,
                event_account.total_device      AS user_agents,
                event_account.total_visit       AS events,
                (
                    SELECT COUNT(DISTINCT event_ip.isp) AS cnt
                    FROM event LEFT JOIN event_ip ON event.ip = event_ip.id
                    WHERE event.account = :user_id AND event.key = :api_key
                )                               AS isps,
                (
                    SELECT COUNT(*) AS cnt
                    FROM event_field_audit_trail
                    WHERE account_id = :user_id AND key = :api_key
                )                               AS edits,
                (
                    SELECT COUNT(*) AS cnt
                    FROM event_session
                    WHERE account_id = :user_id AND key = :api_key
                )                               AS sessions
            FROM event_account
            WHERE
                event_account.id = :user_id AND
                event_account.key = :api_key
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }
}
