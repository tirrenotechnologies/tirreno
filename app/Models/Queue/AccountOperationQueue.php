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

namespace Models\Queue;

class AccountOperationQueue extends \Models\BaseSql {
    public const DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    protected $DB_TABLE_NAME = 'queue_account_operation';

    private \Type\QueueAccountOperationActionType $actionType;

    public function __construct(\Type\QueueAccountOperationActionType $actionType) {
        $this->actionType = $actionType;

        parent::__construct();
    }

    public function add(int $accountId, int $key): void {
        $this->reset();
        $this->event_account = $accountId;
        $this->key = $key;
        $this->action = $this->actionType->value;
        $this->save();
    }

    /**
     * @param array{accountId: int, key: int}[] $accounts
     */
    public function addBatch(array $accounts): void {
        if (count($accounts) === 0) {
            return;
        }

        $params = [':action' => strval($this->actionType->value)];
        $arrayPlaceholders = [];
        $prefix = '';
        foreach ($accounts as $idx => $record) {
            $prefix = ":{$idx}_";

            $params[$prefix . 'idx'] = $idx;
            $params[$prefix . 'account_id'] = intval($record['accountId']);
            $params[$prefix . 'key'] = intval($record['key']);
            $arrayPlaceholders[] = "({$prefix}idx, {$prefix}account_id, {$prefix}key)";
        }

        $strPlaceholders = \implode(', ', $arrayPlaceholders);

        // update waiting records
        $query = (
            "UPDATE queue_account_operation
            SET
                updated = now()
            FROM (VALUES $strPlaceholders) AS v(idx, account_id, key)
            WHERE
                queue_account_operation.event_account   = v.account_id::bigint AND
                queue_account_operation.key             = v.key::bigint AND
                queue_account_operation.action          = :action AND
                queue_account_operation.status          = 'waiting'
            RETURNING v.idx"
        );

        $results = $this->execQuery($query, $params);

        $updatedIdxs = array_unique(array_column($results, 'idx'));
        $notUpdatedIdxs = array_keys(array_diff(array_keys($accounts), $updatedIdxs));

        if (!count($notUpdatedIdxs)) {
            return;
        }

        $params = [':action' => strval($this->actionType->value)];
        $arrayPlaceholders = [];
        foreach ($notUpdatedIdxs as $idxToInsert) {
            $prefix = ":{$idxToInsert}_";
            $record = $accounts[$idxToInsert];

            $params[$prefix . 'account_id'] = $record['accountId'];
            $params[$prefix . 'key'] = $record['key'];
            $arrayPlaceholders[] = "({$prefix}account_id, {$prefix}key, :action)";
        }

        $strPlaceholders = \implode(', ', $arrayPlaceholders);

        $query = "INSERT INTO queue_account_operation (event_account, key, action) VALUES {$strPlaceholders} RETURNING id";

        $result = $this->execQuery($query, $params);

        $msg = sprintf('Adding %s accounts to %s queue -- %s updated, %s inserted', count($accounts), strval($this->actionType->value), count($updatedIdxs), count($result));
        \Utils\Logger::log(null, $msg);
    }

    public function isInQueueStatus(int $accountId, int $key): array {
        $this->reset();
        $this->load([
            'event_account = ? AND key = ? AND action = ? AND status != ?',
            $accountId, $key, $this->actionType->value, \Type\QueueAccountOperationStatusType::Completed,
        ]);

        return $this->dry() ? [false, null] : [true, $this->status];
    }

    public function isInQueue(int $accountId, int $key): bool {
        $this->reset();
        $this->load([
            'event_account = ? AND key = ? AND action = ? AND status != ?',
            $accountId, $key, $this->actionType->value, \Type\QueueAccountOperationStatusType::Completed,
        ]);

        return $this->dry() ? false : true;
    }

    public function isExecuting(): bool {
        $this->load([
            'action = ? AND status = ?',
            $this->actionType->value, \Type\QueueAccountOperationStatusType::Executing,
        ]);

        return $this->dry() ? false : true;
    }

    public function actionIsInQueueProcessing(int $key): bool {
        $this->reset();
        $this->load([
            'key = ? AND action = ? AND status != ? AND status != ?',
            $key,
            $this->actionType->value,
            \Type\QueueAccountOperationStatusType::Completed,
            \Type\QueueAccountOperationStatusType::Failed,
        ]);

        return $this->dry() ? false : true;
    }

    public function getNextInQueue(): array|null {
        $this->reset();
        $this->creator = 'SELECT creator
            FROM dshb_api
            WHERE dshb_api.id = queue_account_operation.key';
        $this->load([
            'action = ? AND status = ?',
            $this->actionType->value, \Type\QueueAccountOperationStatusType::Waiting,
        ], ['order' => 'created ASC']);

        return $this->dry() ? null : $this->cast();
    }

    public function getNextBatchInQueue(int $batchSize): array {
        $params = [
            ':batchSize' => $batchSize,
            ':action' => $this->actionType->value,
            ':status' => \Type\QueueAccountOperationStatusType::Waiting,
        ];

        $query = ('
            SELECT
                queue_account_operation.*,
                dshb_api.creator
            FROM queue_account_operation
            JOIN dshb_api
            ON dshb_api.id = queue_account_operation.key
            WHERE
                action = :action
                AND status = :status
            ORDER BY id ASC
            LIMIT :batchSize
        ');

        return $this->execQuery($query, $params);
    }

    public function getNextBatchKeysInQueue(int $batchSize): array {
        $params = [
            ':batchSize' => $batchSize,
            ':action' => $this->actionType->value,
            ':status' => \Type\QueueAccountOperationStatusType::Waiting,
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
                    action = :action
                    AND status = :status
                ORDER BY id ASC
                LIMIT :batchSize
            ) AS t
        ');

        $results = $this->execQuery($query, $params);

        return array_column($results, 'key');
    }

    public function setWaiting(): void {
        if ($this->loaded()) {
            $now = new \DateTime();
            $this->updated = $now->format(self::DATETIME_FORMAT);
            $this->status = \Type\QueueAccountOperationStatusType::Waiting;
            $this->save();
        }
    }

    /**
     * @param int[] $ids
     */
    public function setWaitingForBatch(array $ids): void {
        $this->setStatusForBatch(
            $ids,
            new \Type\QueueAccountOperationStatusType(\Type\QueueAccountOperationStatusType::Waiting),
        );
    }

    public function setExecuting(): void {
        if ($this->loaded()) {
            $now = new \DateTime();
            $this->updated = $now->format(self::DATETIME_FORMAT);
            $this->status = \Type\QueueAccountOperationStatusType::Executing;
            $this->save();
        }
    }

    /**
     * @param int[] $ids
     */
    public function setExecutingForBatch(array $ids): void {
        $this->setStatusForBatch(
            $ids,
            new \Type\QueueAccountOperationStatusType(\Type\QueueAccountOperationStatusType::Executing),
        );
    }

    public function setCompleted(): void {
        if ($this->loaded()) {
            $now = new \DateTime();
            $this->updated = $now->format(self::DATETIME_FORMAT);
            $this->status = \Type\QueueAccountOperationStatusType::Completed;
            $this->save();
        }
    }

    /**
     * @param int[] $ids
     */
    public function setCompletedForBatch(array $ids): void {
        $this->setStatusForBatch(
            $ids,
            new \Type\QueueAccountOperationStatusType(\Type\QueueAccountOperationStatusType::Completed),
        );
    }

    public function setFailed(): void {
        if ($this->loaded()) {
            $now = new \DateTime();
            $this->updated = $now->format(self::DATETIME_FORMAT);
            $this->status = \Type\QueueAccountOperationStatusType::Failed;
            $this->save();
        }
    }

    /**
     * @param int[] $ids
     */
    public function setFailedForBatch(array $ids): void {
        $this->setStatusForBatch(
            $ids,
            new \Type\QueueAccountOperationStatusType(\Type\QueueAccountOperationStatusType::Failed),
        );
    }

    /**
     * @param int[] $ids
     */
    private function setStatusForBatch(array $ids, \Type\QueueAccountOperationStatusType $status): void {
        if (!count($ids)) {
            return;
        }

        [$params, $placeHolders] = $this->getArrayPlaceholders($ids);

        $params[':status'] = $status->value;
        $params[':updated'] = (new \DateTime())->format(self::DATETIME_FORMAT);

        $query = ("
            UPDATE queue_account_operation
            SET
                status = :status,
                updated = :updated
            WHERE
                id IN ({$placeHolders})
        ");

        $this->execQuery($query, $params);
    }

    public function removeFromQueue(): void {
        if ($this->loaded()) {
            $this->erase();
        }
    }

    public function unclog(): bool {
        if ($this->loaded() && $this->status === \Type\QueueAccountOperationStatusType::Executing) {
            $updatedDateTime = new \DateTime($this->updated);
            $currentDateTime = new \DateTime();

            $differenceInSeconds = $currentDateTime->getTimestamp() - $updatedDateTime->getTimestamp();
            $totalMinutes = (int) floor($differenceInSeconds / 60);

            if ($totalMinutes < \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_AUTO_UNCLOG_AFTER_MINUTES')) {
                return false; // Time not elapsed, no need to unclog (yet).
            }

            $this->setFailed();

            $msg = sprintf('Queue failed on unclog (now - updated > 2 hours) on account %d minutes diff %d.', $this->event_account, $totalMinutes);
            \Utils\Logger::log(null, $msg);

            return true; // Unclogged queue.
        }

        return false; // No need to unclog.
    }

    public function clearCompleted(\DateTime $clearBefore): int {
        $this->reset();

        $params = [
            ':daysAgo' => $clearBefore->format(self::DATETIME_FORMAT),
            ':status' => \Type\QueueAccountOperationStatusType::Completed,
        ];

        $query = ('
            WITH deleted AS
            (
                DELETE FROM queue_account_operation
                WHERE
                    status = :status
                    AND updated < :daysAgo
                    RETURNING *
            ) SELECT count(*) FROM deleted
        ');

        $results = $this->execQuery($query, $params);

        return $results[0]['count'] ?? 0;
    }
}
