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

class UsersStat extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event';

    public function getStatByPeriod(int $apiKey, int $userId, ?array $dateRange = null): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];

        $query = (
            'SELECT
                event_account.userid,
                COUNT(DISTINCT event.id) AS total_visits,
                COUNT(DISTINCT event_ip.id) AS total_ips,
                COUNT(DISTINCT countries.id) AS total_countries,
                COUNT(DISTINCT event_device.id) AS total_devices

            FROM
                event

            LEFT JOIN event_account
            ON event.account = event_account.id

            LEFT JOIN event_url
            ON event.url = event_url.id

            LEFT JOIN event_ip
            ON event.ip = event_ip.id

            LEFT JOIN event_device
            ON event.device = event_device.id

            LEFT JOIN countries
            ON event_ip.country = countries.id

            WHERE
                event.key = :api_key
                AND event_account.userid = :user_id
                %s

            GROUP BY
                event_account.userid'
        );

        $this->applyDateRange($query, $params, $dateRange);

        return $this->execQuery($query, $params);
    }

    private function applyDateRange(string &$query, array &$params, ?array $dateRange = null): void {
        $searchConditions = '';

        if ($dateRange) {
            $searchConditions = (
                'AND event.time >= :start_time
                AND event.time <= :end_time'
            );

            $params[':end_time'] = $dateRange['endDate'];
            $params[':start_time'] = $dateRange['startDate'];
        }

        $query = sprintf($query, $searchConditions);
    }
}
