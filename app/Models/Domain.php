<?php

/**
 * tirreno ~ open security analytics
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

namespace Models;

class Domain extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_domain';

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':api_key' => $apiKey,
            ':domain_id' => $subjectId,
        ];

        $query = (
            'SELECT
                event_domain.id

            FROM
                event_domain

            WHERE
                event_domain.id = :domain_id AND
                event_domain.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getFullDomainInfoById(int $domainId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':domain_id' => $domainId,
        ];

        $query = (
            'SELECT
                event_domain.id,
                event_domain.domain,
                event_domain.total_account,
                event_domain.lastseen,
                event_domain.creation_date,
                event_domain.expiration_date,
                event_domain.disabled,
                event_domain.disposable_domains,
                event_domain.free_email_provider,
                event_domain.tranco_rank,
                event_domain.checked,
                (
                    SELECT COUNT(*)
                    FROM event_email
                    WHERE
                        event_email.domain = event_domain.id AND
                        event_email.key = :api_key AND
                        event_email.fraud_detected IS TRUE
                ) AS fraud

            FROM
                event_domain

            WHERE
                event_domain.id = :domain_id AND
                event_domain.key = :api_key

            GROUP BY
                event_domain.id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function extractById(int $entityId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $entityId,
        ];

        $query = (
            "SELECT
                COALESCE(event_domain.domain, '') AS value

            FROM
                event_domain

            WHERE
                event_domain.id = :id AND
                event_domain.key = :api_key

            LIMIT 1"
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event_email.domain AS id,
                COUNT(DISTINCT(event.account)) AS cnt
            FROM event
            LEFT JOIN event_email
            ON event.email = event_email.id
            WHERE
                event_email.domain IN ({$flatIds}) AND
                event.key = :key AND
                event_email.lastseen > :start_date AND
                event_email.lastseen < :end_date
            GROUP BY event_email.domain"
        );

        $totalAccount = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = ['total_account' => 0];
        }

        foreach ($totalAccount as $rec) {
            $result[$rec['id']]['total_account'] = $rec['cnt'];
        }

        return $result;
    }

    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $extraClause = $force ? '' : ' AND event_domain.lastseen >= event_domain.updated';

        $query = (
            "UPDATE event_domain
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event_email.domain,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS total_account
                FROM event
                LEFT JOIN event_email
                ON event.email = event_email.id
                WHERE
                    event_email.domain IN ($flatIds) AND
                    event.key = :key
                GROUP BY event_email.domain
            ) AS sub
            RIGHT JOIN event_domain sub_domain ON sub.domain = sub_domain.id
            WHERE
                event_domain.id = sub_domain.id AND
                event_domain.id IN ($flatIds) AND
                event_domain.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    public function updateAllTotals(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'UPDATE event_domain
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                updated = date_trunc(\'milliseconds\', now())
            FROM (
                SELECT
                    event_email.domain,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT account) AS total_account
                FROM event
                LEFT JOIN event_email ON event.email = event_email.id
                WHERE
                    event.key = :key
                GROUP BY event_email.domain
            ) AS sub
            RIGHT JOIN event_domain sub_domain ON sub.domain = sub_domain.id
            WHERE
                event_domain.id = sub_domain.id AND
                event_domain.key = :key AND
                event_domain.lastseen >= event_domain.updated'
        );

        return $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                total_visit,
                total_account
            FROM event_domain
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_visit'] = $indexedResult[$item['id']]['total_visit'];
            $item['total_account'] = $indexedResult[$item['id']]['total_account'];
            $res[$idx] = $item;
        }

        return $res;
    }

    public function countNotChecked(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT
                COUNT(*) AS count
            FROM event_domain
            WHERE
                event_domain.key = :key AND
                event_domain.checked IS FALSE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    public function notCheckedExists(int $apiKey): bool {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'SELECT 1
            FROM event_domain
            WHERE
                event_domain.key = :key AND
                event_domain.checked IS FALSE
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return (bool) count($results);
    }

    public function notCheckedForUserId(int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_id' => $userId,
        ];

        $query = (
            'SELECT DISTINCT
                event_domain.id
            FROM event_email
            LEFT JOIN event_domain ON event_email.domain = event_domain.id
            WHERE
                event_email.account_id = :user_id AND
                event_domain.key = :api_key AND
                event_domain.checked IS FALSE'
        );

        return array_column($this->execQuery($query, $params), 'id');
    }
}
