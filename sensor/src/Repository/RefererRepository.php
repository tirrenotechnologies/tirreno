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

use Sensor\Entity\RefererEntity;
use Sensor\Model\Validated\Timestamp;

class RefererRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(RefererEntity $referer): int {
        $sql = 'INSERT INTO event_referer
                (key, referer, lastseen, created)
            VALUES
                (:key, :referer, :lastseen, :created)
            ON CONFLICT (key, referer) DO UPDATE
            SET
                lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $referer->apiKeyId);
        $stmt->bindValue(':referer', $referer->referer);
        $stmt->bindValue(':lastseen', $referer->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $referer->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }
}
