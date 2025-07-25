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

class Map extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'countries';

    public function getAllCountries(?string $dateFrom, ?string $dateTo, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];
        $query = (
            'SELECT
                countries.iso,
                countries.value,
                countries.id,
                COUNT(DISTINCT event.account) AS total_account

            FROM event

            LEFT JOIN event_ip
            ON (event.ip = event_ip.id)

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event.key = :api_key'
        );

        if ($dateTo !== null && $dateFrom !== null) {
            $params[':date_from']   = $dateFrom;
            $params[':date_to']     = $dateTo;

            $query .= ' AND event.time >= :date_from AND event.time <= :date_to';
        }

        $query .= (
            ' GROUP BY
                countries.iso,
                countries.value,
                countries.id'
        );

        return $this->execQuery($query, $params);
    }

    public function getCountriesByIspId(int $ispId, int $apiKey): array {
        $params = [
            ':api_key'      => $apiKey,
            ':id'           => $ispId,
        ];
        $query = (
            'SELECT
                countries.iso,
                countries.value,
                countries.id,
                SUM(event_ip.total_visit) AS total_visit

            FROM event_ip

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event_ip.isp = :id AND
                event_ip.key = :api_key

            GROUP BY
                countries.iso,
                countries.value,
                countries.id'
        );

        return $this->execQuery($query, $params);
    }

    public function getCountriesByDomainId(int $domainId, int $apiKey): array {
        $params = [
            ':api_key'      => $apiKey,
            ':id'           => $domainId,
        ];
        $query = (
            'SELECT
                countries.iso,
                countries.value,
                countries.id,
                COUNT(event.id) AS total_visit

            FROM event

            LEFT JOIN event_ip
            ON (event.ip = event_ip.id)

            LEFT JOIN event_email
            ON (event.email = event_email.id)

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event_email.domain = :id AND
                event_email.key = :api_key

            GROUP BY
                countries.iso,
                countries.value,
                countries.id'
        );

        return $this->execQuery($query, $params);
    }

    public function getCountriesByUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key'      => $apiKey,
            ':id'           => $userId,
        ];
        $query = (
            'SELECT
                countries.iso,
                countries.value,
                countries.id,
                COUNT(event.id) AS total_visit

            FROM event

            LEFT JOIN event_ip
            ON (event.ip = event_ip.id)

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event.account = :id AND
                event.key = :api_key

            GROUP BY
                countries.iso,
                countries.value,
                countries.id'
        );

        return $this->execQuery($query, $params);
    }

    public function getCountriesByResourceId(int $resourceId, int $apiKey): array {
        $params = [
            ':api_key'      => $apiKey,
            ':id'           => $resourceId,
        ];
        $query = (
            'SELECT
                countries.iso,
                countries.value,
                countries.id,
                COUNT(event.id) AS total_visit

            FROM event

            LEFT JOIN event_ip
            ON (event.ip = event_ip.id)

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event.url = :id AND
                event.key = :api_key

            GROUP BY
                countries.iso,
                countries.value,
                countries.id'
        );

        return $this->execQuery($query, $params);
    }

    public function getCountriesByBotId(int $botId, int $apiKey): array {
        $params = [
            ':api_key'      => $apiKey,
            ':id'           => $botId,
        ];
        $query = (
            'SELECT
                countries.iso,
                countries.value,
                countries.id,
                COUNT(event.id) AS total_visit

            FROM event

            LEFT JOIN event_ip
            ON (event.ip = event_ip.id)

            LEFT JOIN event_device
            ON (event.device = event_device.id)

            LEFT JOIN countries
            ON (event_ip.country = countries.id)

            WHERE
                event_device.user_agent = :id AND
                event_device.key = :api_key

            GROUP BY
                countries.iso,
                countries.value,
                countries.id'
        );

        return $this->execQuery($query, $params);
    }
}
