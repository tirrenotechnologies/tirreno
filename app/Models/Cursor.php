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

class Cursor extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'queue_new_events_cursor';

    public function getCursor(): int {
        $query = (
            'SELECT
                last_event_id
            FROM
                queue_new_events_cursor'
        );

        $results = $this->execQuery($query, null);

        return $results[0]['last_event_id'] ?? 0;
    }

    public function getNextCursor(int $currentCursor, int $batchSize = 100): ?int {
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

        $query = (
            'UPDATE
                queue_new_events_cursor
            SET
                last_event_id = :last_event_id'
        );

        $this->execQuery($query, $params);
    }

    public function forceLock(): void {
        $query = (
            'UPDATE
                queue_new_events_cursor
            SET
                locked = TRUE,
                updated = NOW()'
        );

        $this->execQuery($query, null);
    }

    public function safeLock(): bool {
        $lock = $this->getLock();

        if (!count($lock) || !array_key_exists('locked', $lock)) {
            $query = (
                'INSERT INTO queue_new_events_cursor
                    (last_event_id, locked)
                VALUES
                    (-1, TRUE)'
            );

            $this->execQuery($query, null);

            return true;
        }

        if ($lock['locked']) {
            return false;
        }

        $this->forceLock();

        return true;
    }

    public function getLock(): array {
        $query = (
            'SELECT
                locked,
                updated
            FROM
                queue_new_events_cursor
            LIMIT 1'
        );

        return $this->execQuery($query, null)[0] ?? [];
    }

    public function unlock(): void {
        $query = (
            'UPDATE
                queue_new_events_cursor
            SET
                locked = FALSE,
                updated = now()'
        );

        $this->execQuery($query, null);
    }
}
