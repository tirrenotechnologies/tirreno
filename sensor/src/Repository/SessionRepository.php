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
use Sensor\Entity\SessionEntity;

class SessionRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(SessionEntity $session): int {
        $sql = 'INSERT INTO event_session
                (id, key, account_id, lastseen, updated, created, total_visit)
            VALUES
                (:id, :key, :account_id, :lastseen, :updated, :created, :total_visit)
            ON CONFLICT (id) DO UPDATE
            SET
                lastseen = EXCLUDED.lastseen,
                created = CASE WHEN event_session.created > EXCLUDED.lastseen THEN EXCLUDED.lastseen ELSE event_session.created END,
                total_visit = event_session.total_visit + 1
        RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $session->id);
        $stmt->bindValue(':key', $session->apiKeyId);
        $stmt->bindValue(':account_id', $session->accountId);
        $stmt->bindValue(':lastseen', $session->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $session->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $session->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':total_visit', 1);
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }
}
