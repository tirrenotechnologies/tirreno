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

class FieldAudit extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_field_audit';

    public function getFieldById(int $fieldId, int $apiKey): array {
        $params = [
            ':field_id' => $fieldId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                field_id,
                field_name,
                lastseen,
                created

            FROM
                event_field_audit

            WHERE
                event_field_audit.id = :field_id AND
                event_field_audit.key = :api_key
            ORDER BY id DESC
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function checkAccess(int $subjectId, int $apiKey): bool {
        $params = [
            ':api_key' => $apiKey,
            ':field_id' => $subjectId,
        ];

        $query = (
            'SELECT
                event_field_audit.id

            FROM
                event_field_audit

            WHERE
                event_field_audit.id = :field_id AND
                event_field_audit.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    // ajax for grid
    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event_field_audit_trail.field_id AS id,
                COUNT(DISTINCT event_field_audit_trail.event_id) AS cnt
            FROM event_field_audit_trail
            WHERE
                event_field_audit_trail.field_id IN ({$flatIds}) AND
                event_field_audit_trail.key = :key AND
                event_field_audit_trail.created > :start_date AND
                event_field_audit_trail.created < :end_date
            GROUP BY event_field_audit_trail.field_id"
        );

        $totalVisit = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event_field_audit_trail.field_id AS id,
                COUNT(DISTINCT event_field_audit_trail.account_id) AS cnt
            FROM event_field_audit_trail
            WHERE
                event_field_audit_trail.field_id IN ({$flatIds}) AND
                event_field_audit_trail.key = :key AND
                event_field_audit_trail.created > :start_date AND
                event_field_audit_trail.created < :end_date
            GROUP BY event_field_audit_trail.field_id"
        );

        $totalAccount = $this->execQuery($query, $params);

        $query = (
            "SELECT
                event_field_audit_trail.field_id AS id,
                COUNT(event_field_audit_trail.id) AS cnt
            FROM event_field_audit_trail
            WHERE
                event_field_audit_trail.field_id IN ({$flatIds}) AND
                event_field_audit_trail.key = :key AND
                event_field_audit_trail.created > :start_date AND
                event_field_audit_trail.created < :end_date
            GROUP BY event_field_audit_trail.field_id"
        );

        $totalEdit = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = ['total_visit' => 0, 'total_account' => 0];
        }

        foreach ($totalVisit as $rec) {
            $result[$rec['id']]['total_visit'] = $rec['cnt'];
        }

        foreach ($totalAccount as $rec) {
            $result[$rec['id']]['total_account'] = $rec['cnt'];
        }

        foreach ($totalEdit as $rec) {
            $result[$rec['id']]['total_edit'] = $rec['cnt'];
        }

        return $result;
    }

    // partial update for grid
    public function updateTotalsByEntityIds(array $ids, int $apiKey, bool $force = false): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $extraClause = $force ? '' : ' AND event_field_audit.lastseen >= event_field_audit.updated';

        $query = (
            "UPDATE event_field_audit
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                total_edit = COALESCE(sub.total_edit, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event_field_audit_trail.field_id,
                    COUNT(DISTINCT event_field_audit_trail.event_id)    AS total_visit,
                    COUNT(DISTINCT event_field_audit_trail.account_id)  AS total_account,
                    COUNT(event_field_audit_trail.id)                   AS total_edit
                FROM event_field_audit_trail
                WHERE
                    event_field_audit_trail.field_id IN ($flatIds) AND
                    event_field_audit_trail.key = :key
                GROUP BY event_field_audit_trail.field_id
            ) AS sub
            RIGHT JOIN event_field_audit sub_field ON sub.field_id = sub_field.id
            WHERE
                event_field_audit.id = sub_field.id AND
                event_field_audit.id IN ($flatIds) AND
                event_field_audit.key = :key
                $extraClause"
        );

        $this->execQuery($query, $params);
    }

    // cron update
    public function updateAllTotals(int $apiKey): int {
        $params = [
            ':key' => $apiKey,
        ];

        $query = (
            'UPDATE event_field_audit
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_account = COALESCE(sub.total_account, 0),
                total_edit = COALESCE(sub.total_edit, 0),
                updated = date_trunc(\'milliseconds\', now())
            FROM (
                SELECT
                    event_field_audit_trail.field_id,
                    COUNT(DISTINCT event_field_audit_trail.event_id)    AS total_visit,
                    COUNT(DISTINCT event_field_audit_trail.account_id)  AS total_account,
                    COUNT(event_field_audit_trail.id)                   AS total_edit
                FROM event_field_audit_trail
                WHERE
                    event_field_audit_trail.key = :key
                GROUP BY event_field_audit_trail.field_id
            ) AS sub
            RIGHT JOIN event_field_audit sub_field ON sub.field_id = sub_field.id
            WHERE
                event_field_audit.id = sub_field.id AND
                event_field_audit.key = :key AND
                event_field_audit.lastseen >= event_field_audit.updated'
        );

        return $this->execQuery($query, $params);
    }

    // partial for grid
    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'field_audit_id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                total_visit,
                total_account,
                total_edit
            FROM event_field_audit
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_visit'] = $indexedResult[$item['field_audit_id']]['total_visit'];
            $item['total_account'] = $indexedResult[$item['field_audit_id']]['total_account'];
            $item['total_edit'] = $indexedResult[$item['field_audit_id']]['total_edit'];
            $res[$idx] = $item;
        }

        return $res;
    }
}
