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

class Users extends \Tirreno\Models\Base {
    protected string $tableName = 'event_account';

    private function coreQuery(): string {
        return (
            'SELECT
                event_account.id AS accountid,
                event_account.userid,
                event_account.lastseen,
                event_account.created,
                event_account.firstname,
                event_account.lastname,
                event_account.score,
                event_account.score_details,
                event_account.score_updated_at,
                event_account.is_important,
                event_account.fraud,
                event_account.reviewed,
                event_account.latest_decision,
                event_account.added_to_review,

                event_email.email

            FROM
                event_account

            LEFT JOIN event_email
            ON (event_account.lastemail = event_email.id) '
        );
    }

    private function sortQuery(?array $orderBy = null, ?int $limit = null, ?int $offset = null): string {
        $orderQuery = null;
        $limitQuery = null;
        $offsetQuery = null;

        if ($orderBy !== null) {
            $orders = [];

            foreach ($orderBy as $order) {
                if (count($order) === 2 && preg_match('/^[a-z._]+$/', $order[0]) && in_array(strtoupper($order[1]), ['ASC', 'DESC'])) {
                    $orders[] = $order[0] . ' ' . strtoupper($order[1]);
                }
            }

            if ($orders) {
                $orderQuery = 'ORDER BY ' . implode(', ', $orders);
            }
        }

        if ($limit !== null) {
            $limitQuery = 'LIMIT ' . strval($limit);
        }

        if ($offset !== null) {
            $offsetQuery = 'OFFSET ' . strval($offset);
        }

        return ' ' . implode(' ', array_filter([$orderQuery, $limitQuery, $offsetQuery]));
    }

    public function getUsersByIpId(int $ipId, int $apiKey, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        $params = [
            ':ip_id'    => $ipId,
            ':api_key'  => $apiKey,
        ];

        $query = $this->coreQuery() . (
            'LEFT JOIN event
            ON event.account = event_account.id

            WHERE
                event.ip = :ip_id AND
                event_account.key = :api_key'
        ) . $this->sortQuery($orderBy, $limit, $offset);

        return $this->execQuery($query, $params);
    }

    public function getUsersByEventTimeRange(int $start, int $end, int $apiKey, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        $params = [
            ':start'    => $start,
            ':end'      => $end,
            ':api_key'  => $apiKey,
        ];

        $query = $this->coreQuery() . (
            'LEFT JOIN event
            ON event.account = event_account.id

            WHERE
                event.time >= :start AND
                event.time <= :end AND
                event_account.key = :api_key'
        ) . $this->sortQuery($orderBy, $limit, $offset);

        return $this->execQuery($query, $params);
    }


    public function getUsersByLastseenTimeRange(int $start, int $end, int $apiKey, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array {
        $params = [
            ':start'    => $start,
            ':end'      => $end,
            ':api_key'  => $apiKey,
        ];

        $query = $this->coreQuery() . (
            'WHERE
                event_account.lastseen >= :start AND
                event_account.lastseen <= :end AND
                event_account.key = :api_key'
        ) . $this->sortQuery($orderBy, $limit, $offset);

        return $this->execQuery($query, $params);
    }


    public function getLastNUsers(int $limit, int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
            ':limit'    => $limit,
        ];

        $query = (
            'SELECT
                event_account.id AS accountid,
                event_account.userid AS accounttitle,
                event_account.lastseen,
                event_email.email

            FROM
                event_account

            LEFT JOIN event_email
            ON event_account.lastemail = event_email.id

            WHERE
                event_account.key = :api_key

            ORDER BY event_account.lastseen DESC
            LIMIT :limit'
        );

        return $this->execQuery($query, $params);
    }

    public function getTotalUsers(int $apiKey): int {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(event_account.id)

            FROM
                event_account

            WHERE
                event_account.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedUsers(int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT DISTINCT
                event.account AS id
            FROM
                event
            LEFT JOIN event_ip ON event.ip = event_ip.id
            WHERE
                event.key = :api_key AND
                event_ip.checked IS FALSE'
        );
        $result = array_column($this->execQuery($query, $params), 'id');

        // email + domain
        $query = (
            'SELECT DISTINCT
                event_email.account_id AS id
            FROM
                event_email
            LEFT JOIN event_domain ON event_email.domain = event_domain.id
            WHERE
                event_email.key = :api_key AND
                (event_email.checked IS FALSE OR event_domain.checked IS FALSE)'
        );
        $result = array_merge($result, array_column($this->execQuery($query, $params), 'id'));

        // phone
        $query = (
            'SELECT DISTINCT
                event_phone.account_id AS id
            FROM
                event_phone
            WHERE
                event_phone.key = :api_key AND
                event_phone.checked IS FALSE'
        );
        $result = array_merge($result, array_column($this->execQuery($query, $params), 'id'));

        // device
        $query = (
            'SELECT DISTINCT
                event_device.account_id AS id
            FROM
                event_device
            LEFT JOIN event_ua_parsed ON event_device.user_agent = event_ua_parsed.id
            WHERE
                event_device.key = :api_key AND
                event_ua_parsed.checked IS FALSE'
        );
        $result = array_merge($result, array_column($this->execQuery($query, $params), 'id'));

        return array_unique($result);
    }
}
