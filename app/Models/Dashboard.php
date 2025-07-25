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

class Dashboard extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_account';

    public function getTotalBlockedUsers(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT(event_account.id)

            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.fraud IS TRUE'
        );

        $field = 'event_account.latest_decision';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    public function getTotalUsersForReview(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT(event_account.id)

            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.fraud IS NULL AND
                event_account.added_to_review IS NOT NULL'
        );

        $field = 'event_account.added_to_review';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    public function getTotalEvents(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT(*)

            FROM
                event

            WHERE
                event.key = :api_key'
        );

        $field = 'event.time';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    public function getTotalResources(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT(*)

            FROM
                event_url

            WHERE
                event_url.key = :api_key'
        );

        $field = 'event_url.lastseen';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    public function getTotalCountries(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT(event_country.id)

            FROM
                event_country

            WHERE
                event_country.key = :api_key'
        );

        $field = 'event_country.lastseen';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    public function getTotalIps(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT (*)

            FROM
                event_ip

            WHERE
                event_ip.key = :api_key'
        );

        $field = 'event_ip.lastseen';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    public function getTotalUsers(?array $dateRange, int $apiKey): int {
        $query = (
            'SELECT
                COUNT (*)

            FROM
                event_account

            WHERE
                event_account.key = :api_key'
        );

        $field = 'event_account.lastseen';

        return $this->getTotal($query, $field, $dateRange, $apiKey);
    }

    private function getTotal(string $query, string $dateField, ?array $dateRange, int $apiKey): int {
        $params = [
            ':api_key' => $apiKey,
        ];

        if ($dateRange) {
            $params[':end_time'] = $dateRange['endDate'];
            $params[':start_time'] = $dateRange['startDate'];

            $query .= " AND {$dateField} >= :start_time AND {$dateField} <= :end_time";
        }

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }
}
