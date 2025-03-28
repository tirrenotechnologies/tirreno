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

class QueueNewEventsCursor extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'queue_new_events_cursor';

    public function getCursor(): int {
        $query = ('SELECT
                last_event_id
            FROM
                queue_new_events_cursor;
        ');

        $results = $this->execQuery($query, null);

        return $results[0]['last_event_id'] ?? 0;
    }

    public function getNextCursor(int $currentCursor, int $batchSize = 100): int|null {
        $params = [
            ':current_cursor' => $currentCursor,
            ':batch_size' => $batchSize,
        ];

        $query = ('WITH numbered_events AS (
                SELECT
                    id,
                    ROW_NUMBER() OVER (ORDER BY id) AS rownum
                FROM
                    event
                WHERE
                    event.id > :current_cursor
                LIMIT :batch_size
            )
            SELECT
                id AS next_cursor
            FROM
                numbered_events
            ORDER BY
                rownum DESC
            LIMIT 1;
        ');

        $results = $this->execQuery($query, $params);

        return $results[0]['next_cursor'] ?? null;
    }

    public function updateCursor(int $lastEventId): void {
        $params = [
            ':last_event_id' => $lastEventId,
        ];

        $query = ('UPDATE
                queue_new_events_cursor
            SET
                last_event_id = :last_event_id;
        ');

        $this->execQuery($query, $params);
    }

    public function acquireLock(): bool {
        $this->obtainLockState();

        if ($this->locked) {
            return false;                   // lock is busy
        }

        if ($this->locked === null) {       // insert cursor row and acquire lock
            $query = (
                'INSERT INTO queue_new_events_cursor
                    (last_event_id, locked)
                VALUES
                    (-1, TRUE)'
            );
        } else {                            // locked is False, acquire lock
            $query = (
                'UPDATE
                    queue_new_events_cursor
                SET
                    locked = TRUE,
                    updated = NOW()'
            );
        }

        $this->execQuery($query, null);

        return true;                        // lock acquired
    }

    private function obtainLockState(): void {
        $query = ('SELECT
                locked,
                updated
            FROM
                queue_new_events_cursor;
        ');

        $results = $this->execQuery($query, null);

        $this->locked = $results[0]['locked'] ?? null;
        $this->updated = $results[0]['updated'] ?? null;
    }

    public function releaseLock(): void {
        $query = ('UPDATE
                queue_new_events_cursor
            SET
                locked = FALSE,
                updated = now();
        ');
        $this->execQuery($query, null);
    }

    public function unclog(): bool {
        $this->obtainLockState();

        if ($this->locked) {
            $updatedDateTime = new \DateTime($this->updated);
            $currentDateTime = new \DateTime();

            $differenceInSeconds = $currentDateTime->getTimestamp() - $updatedDateTime->getTimestamp();
            $totalMinutes = floor($differenceInSeconds / 60);

            if ($totalMinutes < \Utils\Constants::get('ACCOUNT_OPERATION_QUEUE_AUTO_UNCLOG_AFTER_MINUTES')) {
                return false; // Time not elapsed, no need to unclog (yet).
            }

            $this->releaseLock();

            return true; // Unclogged cursor.
        }

        return false; // No need to unclog.
    }
}
