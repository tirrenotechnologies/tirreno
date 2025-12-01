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

use Sensor\Model\Validated\Timestamp;

class EventIncorrectRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    /**
     * @param array<string, string> $payload
     */
    public function logIncorrectEvent(array $payload, string $error, ?string $traceId, ?int $apiKeyId): void {
        $now = new \DateTimeImmutable();

        $sql = 'INSERT INTO event_incorrect (
                   payload, created, errors, traceid, key
               ) VALUES (
                   :payload, :created, :errors, :traceid, :key
               )';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':payload', json_encode($payload, \JSON_THROW_ON_ERROR));
        $stmt->bindValue(':created', $now->format(Timestamp::FORMAT));
        $stmt->bindValue(':errors', json_encode([$error], \JSON_THROW_ON_ERROR));
        $stmt->bindValue(':traceid', $traceId);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->execute();
    }
}
