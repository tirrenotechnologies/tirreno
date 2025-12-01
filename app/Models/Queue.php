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

class Queue extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'queue_account_operation';

    public function add(int $accountId, string $action, int $key): void {
        $params = [
            ':account' => $accountId,
            ':action'   => $action,
            ':key'      => $key,
        ];

        $query = (
            'INSERT INTO queue_account_operation
            (event_account, action, key)
            VALUES 
            (:account, :action::queue_account_operation_action, :key)'
        );

        $this->execQuery($query, $params);
    }
    private function setStatus(string $status, array $ids): void {
        if (!count($ids)) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':status'] = $status;

        $query = (
            "UPDATE
                queue_account_operation
            SET
                status = :status,
                updated = now()
            WHERE id IN ({$flatIds})"
        );

        $this->execQuery($query, $params);
    }

    public function setWaiting(array $ids): void {
        $this->setStatus(\Utils\Constants::get('WAITING_QUEUE_STATUS_TYPE'), $ids);
    }

    public function setFailed(array $ids): void {
        $this->setStatus(\Utils\Constants::get('FAILED_QUEUE_STATUS_TYPE'), $ids);
    }

    public function setCompleted(array $ids): void {
        $this->setStatus(\Utils\Constants::get('COMPLETED_QUEUE_STATUS_TYPE'), $ids);
    }

    public function setExecuting(array $ids): void {
        $this->setStatus(\Utils\Constants::get('EXECUTING_QUEUE_STATUS_TYPE'), $ids);
    }

    public function isInQueueStatus(int $accountId, string $action, int $key): array {
        $params = [
            ':account'  => $accountId,
            ':status'   => \Utils\Constants::get('COMPLETED_QUEUE_STATUS_TYPE'),
            ':key'      => $key,
            ':action'   => $action,
        ];

        $query = (
            'SELECT
                status
            FROM
                queue_account_operation
            WHERE
                action = :action::queue_account_operation_action AND
                status != :status::queue_account_operation_status AND
                event_account = :account AND
                key = :key
            ORDER BY updated DESC
            LIMIT 1'
        );

        $result = $this->execQuery($query, $params)[0] ?? [];

        return !$result ? [false, null] : [true, $result['status']];
    }

    public function isInQueue(int $accountId, string $action, int $key): bool {
        return $this->isInQueueStatus($accountId, $action, $key)[0];
    }

    public function actionIsInQueueProcessing(string $action, int $key): bool {
        $params = [
            ':failed'       => \Utils\Constants::get('FAILED_QUEUE_STATUS_TYPE'),
            ':completed'    => \Utils\Constants::get('COMPLETED_QUEUE_STATUS_TYPE'),
            ':key'          => $key,
            ':action'       => $action,
        ];

        $query = (
            'SELECT
                1
            FROM
                queue_account_operation
            WHERE
                action = :action::queue_account_operation_action AND
                status != :completed::queue_account_operation_status AND
                status != :failed::queue_account_operation_status AND
                key = :key
            ORDER BY updated DESC
            LIMIT 1'
        );

        return boolval(count($this->execQuery($query, $params)));
    }

    public function removeFromQueue(int $accountId, string $action, int $key): void {
        $params = [
            ':account'      => $accountId,
            ':key'          => $key,
            ':action'       => $action,
        ];

        $query = (
            'DELETE FROM queue_account_operation
            WHERE
                event_account = :account AND
                action = :action::queue_account_operation_action AND
                key = :key'
        );

        $this->execQuery($query, $params);
    }

    public function addBatch(array $accounts, string $action): void {
        if (count($accounts) === 0) {
            return;
        }

        $params = [
            ':action'   => $action,
            ':waiting'  => \Utils\Constants::get('WAITING_QUEUE_STATUS_TYPE'),
        ];

        $arrayPlaceholders = [];
        $prefix = '';
        foreach ($accounts as $idx => $record) {
            $prefix = ":{$idx}_";

            $params[$prefix . 'idx']        = $idx;
            $params[$prefix . 'account_id'] = intval($record['accountId']);
            $params[$prefix . 'key']        = intval($record['key']);
            $arrayPlaceholders[]            = "({$prefix}idx, {$prefix}account_id, {$prefix}key)";
        }

        $strPlaceholders = implode(', ', $arrayPlaceholders);

        // update waiting records
        $query = (
            "UPDATE queue_account_operation
            SET
                updated = now()
            FROM (VALUES $strPlaceholders) AS v(idx, account_id, key)
            WHERE
                queue_account_operation.event_account   = v.account_id::bigint AND
                queue_account_operation.key             = v.key::bigint AND
                queue_account_operation.action          = :action::queue_account_operation_action AND
                queue_account_operation.status          = :waiting::queue_account_operation_status
            RETURNING v.idx"
        );

        $results = $this->execQuery($query, $params);

        $updatedIdxs = array_unique(array_column($results, 'idx'));
        $notUpdatedIdxs = array_keys(array_diff(array_keys($accounts), $updatedIdxs));

        if (!count($notUpdatedIdxs)) {
            return;
        }

        $params = [':action' => $action];

        $arrayPlaceholders = [];
        foreach ($notUpdatedIdxs as $idxToInsert) {
            $prefix = ":{$idxToInsert}_";
            $record = $accounts[$idxToInsert];

            $params[$prefix . 'account_id'] = $record['accountId'];
            $params[$prefix . 'key'] = $record['key'];
            $arrayPlaceholders[] = "({$prefix}account_id, {$prefix}key, :action)";
        }

        $strPlaceholders = implode(', ', $arrayPlaceholders);

        $query = (
            "INSERT INTO queue_account_operation
            (event_account, key, action)
            VALUES {$strPlaceholders} 
            RETURNING id"
        );

        $result = $this->execQuery($query, $params);
        // TODO: add msg about adding account to queue with counters?
    }


    public function addBatchIds(array $accountIds, string $action, int $key): void {
        $batchSize = \Utils\Variables::getAccountOperationQueueBatchSize();

        $batch = [];
        $cnt = 0;

        foreach ($accountIds as $id) {
            $batch[] = [
                'accountId' => $id,
                'key' => $key,
            ];
            $cnt++;

            if ($cnt >= $batchSize) {
                $this->addBatch($batch, $action);
                $batch = [];
                $cnt = 0;
            }
        }

        if ($cnt) {
            $this->addBatch($batch, $action);
        }
    }


    public function clearQueue(string $action, string $before): int {
        $params = [
            ':before' => $before,
            ':status' => \Utils\Constants::get('COMPLETED_QUEUE_STATUS_TYPE'),
            ':action' => $action,
        ];

        $query = ('
            WITH deleted AS
            (
                DELETE FROM queue_account_operation
                WHERE
                    status = :status::queue_account_operation_status AND
                    action = :action::queue_account_operation_action AND
                    updated < :before
                    RETURNING id
            ) SELECT COUNT(*) FROM deleted
        ');

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }

    // add updated ts limit param?
    public function setFailedForStuckAction(string $action): void {
        $params = [
            ':action'   => $action,
            ':status'   => \Utils\Constants::get('FAILED_QUEUE_STATUS_TYPE'),
            ':stuck'    => \Utils\Constants::get('EXECUTING_QUEUE_STATUS_TYPE'),
        ];

        $query = (
            'UPDATE
                queue_account_operation
            SET
                status = :status,
                updated = now()
            WHERE
                action = :action::queue_account_operation_action AND
                status = :stuck::queue_account_operation_status'
        );

        $this->execQuery($query, $params);
    }

    public function checkExecuting(string $action): array {
        $params = [
            ':action'   => $action,
            ':status'   => \Utils\Constants::get('EXECUTING_QUEUE_STATUS_TYPE'),
        ];

        $query = (
            'SELECT
                event_account,
                updated
            FROM
                queue_account_operation
            WHERE
                action = :action::queue_account_operation_action AND
                status = :status::queue_account_operation_status
            ORDER BY updated ASC
            LIMIT 1'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function getNextBatchInQueue(string $action, int $size): array {
        $params = [
            ':batch'    => $size,
            ':action'   => $action,
            ':status'   => \Utils\Constants::get('WAITING_QUEUE_STATUS_TYPE'),
        ];

        $query = ('
            SELECT
                queue_account_operation.id,
                queue_account_operation.event_account,
                queue_account_operation.key,
                dshb_api.creator
            FROM queue_account_operation
            JOIN dshb_api
            ON dshb_api.id = queue_account_operation.key
            WHERE
                action = :action::queue_account_operation_action AND
                status = :status::queue_account_operation_status
            ORDER BY queue_account_operation.id ASC
            LIMIT :batch
        ');

        return $this->execQuery($query, $params);
    }

    public function getNextBatchKeys(string $action, int $size): array {
        $params = [
            ':batch'    => $size,
            ':action'   => $action,
            ':status'   => \Utils\Constants::get('WAITING_QUEUE_STATUS_TYPE'),
        ];

        $query = ('
            SELECT
                DISTINCT key
            FROM (
                SELECT
                    queue_account_operation.id,
                    queue_account_operation.key
                FROM queue_account_operation
                WHERE
                action = :action::queue_account_operation_action AND
                status = :status::queue_account_operation_status
                ORDER BY queue_account_operation.id ASC
                LIMIT :batch
            ) AS t
        ');

        $results = $this->execQuery($query, $params);

        return array_column($results, 'key');
    }
}
