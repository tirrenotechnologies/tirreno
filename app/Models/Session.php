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

namespace Models;

class Session extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_session';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $query = (
            'SELECT
                event_session.id

            FROM
                event_session

            WHERE
                event_session.key    = :api_key
                AND event_session.id = :session_id'
        );

        $params = [
            ':api_key' => $apiKey,
            ':session_id' => $subjectId,
        ];

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function updateTotalsByAccountIds(array $ids, int $apiKey): int {
        if (!count($ids)) {
            return 0;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;

        $query = (
            "UPDATE event_session
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_ip = COALESCE(sub.total_ip, 0),
                total_device = COALESCE(sub.total_device, 0),
                total_country = COALESCE(sub.total_country, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.session_id,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT event.ip) AS total_ip,
                    COUNT(DISTINCT event.device) AS total_device,
                    COUNT(DISTINCT event_ip.country) AS total_country
                FROM event
                LEFT JOIN event_ip
                ON event.ip = event_ip.id
                WHERE
                    event.account IN ($flatIds) AND
                    event.key = :key
                GROUP BY event.session_id
            ) AS sub
            RIGHT JOIN event_session sub_session ON sub.session_id = sub_session.id
            WHERE
                event_session.id = sub.session_id AND
                event_session.account_id IN ($flatIds) AND
                event_session.key = :key AND
                event_session.lastseen >= event_session.updated"
        );

        return $this->execQuery($query, $params);
    }
}
