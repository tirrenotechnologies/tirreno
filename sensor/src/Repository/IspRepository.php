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

use Sensor\Entity\IspEntity;
use Sensor\Entity\IspLocalhostEntity;
use Sensor\Entity\IspEnrichedEntity;
use Sensor\Model\Validated\Timestamp;

class IspRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function update(IspEntity|IspEnrichedEntity $isp, int $ispId): void {
        $sql = 'UPDATE event_isp SET lastseen = :lastseen WHERE event_isp.id = :id AND event_isp.key = :key';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $isp->apiKeyId);
        $stmt->bindValue(':id', $ispId);
        $stmt->bindValue(':lastseen', $isp->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();
    }

    public function insert(IspEnrichedEntity|IspLocalhostEntity|IspEntity $isp): int {
        $sql = 'INSERT INTO event_isp
                (key, asn, name, description, lastseen, created, updated)
            VALUES
                (:key, :asn, :name, :description, :lastseen, :created, :updated)
            ON CONFLICT (key, asn) DO UPDATE
            SET
                name = EXCLUDED.name, description = EXCLUDED.description, lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $isp->apiKeyId);
        $stmt->bindValue(':asn', $isp->asn);
        $stmt->bindValue(':name', $isp->name);
        $stmt->bindValue(':description', $isp->description);
        $stmt->bindValue(':lastseen', $isp->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $isp->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $isp->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }
}
