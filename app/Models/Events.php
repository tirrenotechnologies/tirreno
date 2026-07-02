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

namespace Tirreno\Models;

class Events extends \Tirreno\Models\Base {
    protected string $tableName = 'event';

    public function getDistinctAccountsVisitLimit(int $after, int $until): array {
        $params = [
            ':cursor'       => $after,
            ':next_cursor'  => $until,
            ':visit_limit'  => tirreno('utils')->variables->getUserQueueEventsLimit(),
        ];

        $query = (
            'SELECT DISTINCT
                event.account AS "accountId",
                event.key
            FROM
                event
            JOIN
                event_account ON event.account = event_account.id
            WHERE
                event.id > :cursor AND
                event.id <= :next_cursor AND
                (event_account.total_visit IS NULL OR :visit_limit = 0 OR event_account.total_visit < :visit_limit)'
        );

        return $this->execQuery($query, $params);
    }

    public function getDistinctAccounts(int $after, int $until): array {
        $params = [
            ':cursor'       => $after,
            ':next_cursor'  => $until,
        ];

        $query = (
            'SELECT DISTINCT
                event.account AS "accountId",
                eve
                event.key
            FROM
                event
            JOIN
                event_account ON event.account = event_account.id
            WHERE
                event.id > :cursor AND
                event.id <= :next_cursor'
        );

        return $this->execQuery($query, $params);
    }

    protected function applyLimit(string &$query, array &$queryParams): void {
        $start = tirreno('utils')->conversion->getIntRequestParam('start');
        $length = tirreno('utils')->conversion->getIntRequestParam('length');

        if (isset($start) && isset($length)) {
            $query .= ' LIMIT :length OFFSET :start';

            $queryParams[':start'] = $start;
            $queryParams[':length'] = $length;
        }
    }

    public function uniqueEntitiesByUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];
        $query = (
            'SELECT DISTINCT
                event.ip,
                event_ip.isp,
                event_ip.country,
                event.url,
                event_email.domain,
                event_phone.phone_number
            FROM
                event
            LEFT JOIN event_ip
            ON event.ip = event_ip.id
            LEFT JOIN event_email
            ON event.email = event_email.id
            LEFT JOIN event_phone
            ON event.phone = event_phone.id
            LEFT JOIN event_country
            ON event_ip.country = event_country.country AND event_ip.key = event_country.key

            WHERE
                event.key = :api_key AND
                event.account = :user_id'
        );

        $results = $this->execQuery($query, $params);

        return [
            'ip_ids' => array_unique(array_column($results, 'ip')),
            'isp_ids' => array_unique(array_column($results, 'isp')),
            'country_ids' => array_unique(array_column($results, 'country')),
            'url_ids' => array_unique(array_column($results, 'url')),
            'domain_ids' => array_unique(array_column($results, 'domain')),
            'phone_numbers' => array_unique(array_column($results, 'phone_number')),
        ];
    }

    public function retentionDeletion(int $weeks, int $apiKey): int {
        // insuring clause
        if ($weeks < 1) {
            return 0;
        }

        $params = [
            ':api_key'  => $apiKey,
            ':weeks'    => $weeks,
            ':week_sec' => tirreno('utils')->constants->SECONDS_IN_WEEK,
        ];

        $query = (
            'DELETE FROM event
            WHERE
                event.key = :api_key AND
                (EXTRACT(EPOCH FROM (NOW() - event.time)) / :week_sec) >= :weeks'
        );

        return $this->execQuery($query, $params);
    }
}
