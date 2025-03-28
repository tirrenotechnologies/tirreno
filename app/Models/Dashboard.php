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
    use \Traits\DateRange;

    private $API_KEY = null;
    protected $DB_TABLE_NAME = 'event_account';

    public function getStatWithDateRange(int $apiKey): array {
        return $this->getStat($apiKey, true);
    }

    public function getStatWithoutDateRange(int $apiKey): array {
        return $this->getStat($apiKey, false);
    }

    private function getStat(int $apiKey, bool $useDates): array {
        $this->API_KEY = $apiKey;
        $this->USE_DATES = $useDates;

        // $model = new \Models\ApiKeys();
        // $model->getKeyById($apiKey);
        // $reviewQueueThreshold = $model->review_queue_threshold;
        $reviewQueueThreshold = \Utils\Constants::USER_LOW_SCORE_SUP;

        $ips = $this->getTotalIps();
        $users = $this->getTotalUsers();
        $events = $this->getTotalEvents();
        $countries = $this->getTotalCountries();
        $resources = $this->getTotalResources();
        $blockedUsers = $this->getTotalBlockedUsers();
        $usersForReview = $this->getTotalUsersForReview($reviewQueueThreshold);

        return [
            'ips' => $ips,
            'users' => $users,
            'events' => $events,
            'countries' => $countries,
            'resources' => $resources,
            'blockedUsers' => $blockedUsers,
            'usersForReview' => $usersForReview,
        ];
    }

    private function getTotalBlockedUsers(): int {
        $query = (
            'SELECT
                COUNT(*)

            FROM
                event_account

            WHERE
                event_account.key = :api_key AND
                event_account.fraud IS TRUE
                %s'
        );

        $field = 'event_account.latest_decision';

        return $this->getTotal($query, $field);
    }

    private function getTotalUsersForReview(int $lowScore): int {
        $query = (
            'SELECT
                COUNT(event_account.id)

            FROM
                event_account

            WHERE
                event_account.key = :api_key
                AND
                (
                    event_account.reviewed = :reviewed
                    OR event_account.fraud IS NULL
                )
                AND event_account.score <= :low_score
                %s'
        );

        $additionalParams = [
            ':reviewed'     => false,
            ':low_score'    => $lowScore,
        ];

        $field = 'event_account.created';

        return $this->getTotal($query, $field, $additionalParams);
    }

    private function getTotalEvents(): int {
        $query = (
            'SELECT
                COUNT(event.id)

            FROM
                event

            WHERE
                event.key = :api_key
                %s'
        );

        $field = 'event.time';

        return $this->getTotal($query, $field);
    }

    private function getTotalResources(): int {
        $query = (
            'SELECT
                COUNT(event_url.id)

            FROM
                event_url

            WHERE
                event_url.key = :api_key
                %s'
        );

        $field = 'event_url.lastseen';

        return $this->getTotal($query, $field);
    }

    private function getTotalCountries(): int {
        $query = (
            'SELECT
                COUNT(event_country.id)

            FROM
                event_country

            WHERE
                event_country.key = :api_key
                %s'
        );

        $field = 'event_country.lastseen';

        return $this->getTotal($query, $field);
    }

    private function getTotalIps(): int {
        $query = (
            'SELECT
                COUNT (event_ip.ip)

            FROM
                event_ip

            WHERE
                event_ip.key = :api_key
                %s'
        );

        $field = 'event_ip.lastseen';

        return $this->getTotal($query, $field);
    }

    private function getTotalUsers(): int {
        $query = (
            'SELECT
                COUNT (event_account.id)

            FROM
                event_account

            WHERE
                event_account.key = :api_key
                %s'
        );

        $field = 'event_account.lastseen';

        return $this->getTotal($query, $field);
    }

    private function getTotal(string $query, string $dateField, array $additionalParams = []): int {
        $request = $this->f3->get('REQUEST');
        $dateRange = $this->getDatesRange($request);

        $apiKey = $this->API_KEY;
        $useDates = $this->USE_DATES;

        $search = '';
        $params = [':api_key' => $apiKey];
        $params = array_merge($params, $additionalParams);

        if ($useDates && $dateRange) {
            $params[':end_time'] = $dateRange['endDate'];
            $params[':start_time'] = $dateRange['startDate'];

            $search = ("
                AND {$dateField} >= :start_time
                AND {$dateField} <= :end_time
            ");
        }

        $query = sprintf($query, $search);

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }
}
