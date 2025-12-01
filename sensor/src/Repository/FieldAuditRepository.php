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

namespace Sensor\Repository;

use Sensor\Entity\PayloadEntity;
use Sensor\Model\Validated\Timestamp;

class FieldAuditRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(?PayloadEntity $payload, int $eventId): array {
        if ($payload === null) {
            return [];
        }

        $ids = [];

        foreach ($payload->payload as $item) {
            $sql = 'INSERT INTO event_field_audit
                    (key, field_id, field_name, lastseen, created, updated)
                VALUES (:key, :field_id, :field_name, :lastseen, :created, :updated)
                ON CONFLICT (key, field_id) DO UPDATE
                SET
                    field_name = COALESCE(EXCLUDED.field_name, event_field_audit.field_name), lastseen = EXCLUDED.lastseen
                RETURNING id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':key', $payload->apiKeyId);
            $stmt->bindValue(':lastseen', $payload->lastSeen->format(Timestamp::EVENTFORMAT));
            $stmt->bindValue(':created', $payload->lastSeen->format(Timestamp::EVENTFORMAT));
            $stmt->bindValue(':updated', $payload->lastSeen->format(Timestamp::EVENTFORMAT));
            $stmt->bindValue(':field_id', $item['field_id']);
            $stmt->bindValue(':field_name', $item['field_name']);
            $stmt->execute();

            /** @var array{id: int} $result */
            $result = $stmt->fetch();

            $ids[] = $result['id'];
        }

        return $ids;
    }
}
