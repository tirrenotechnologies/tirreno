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

declare(strict_types=1);

namespace Sensor\Repository;

use Sensor\Entity\PayloadEntity;
use Sensor\Model\Validated\Timestamp;

class FieldAuditTrailRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(?PayloadEntity $payload, int $eventId): int {
        if ($payload === null) {
            return 0;
        }

        $cnt = 0;

        foreach ($payload->payload as $item) {
            $sql = 'INSERT INTO event_field_audit_trail
                    (account_id, key, created, event_id, field_id, field_name, old_value, new_value, parent_id, parent_name)
                VALUES
                    (:account_id, :key, :created, :event_id, :field_id, :field_name, :old_value, :new_value, :parent_id, :parent_name)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':account_id', $payload->accountId);
            $stmt->bindValue(':key', $payload->apiKeyId);
            $stmt->bindValue(':created', $payload->lastSeen->format(Timestamp::EVENTFORMAT));
            $stmt->bindValue(':event_id', $eventId);
            $stmt->bindValue(':field_id', $item['field_id']);
            $stmt->bindValue(':field_name', $item['field_name']);
            $stmt->bindValue(':old_value', $item['old_value']);
            $stmt->bindValue(':new_value', $item['new_value']);
            $stmt->bindValue(':parent_id', $item['parent_id']);
            $stmt->bindValue(':parent_name', $item['parent_name']);
            $stmt->execute();
            $cnt++;
        }

        return $cnt;
    }
}
