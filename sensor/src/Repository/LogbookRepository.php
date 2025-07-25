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

use Sensor\Entity\LogbookEntity;

class LogbookRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(LogbookEntity $request): void {
        $sql = 'INSERT INTO event_logbook
                (key, ip, event, error_type, error_text, raw, started)
            VALUES
                (:key, :ip, :event, :error_type, :error_text, :raw, :started)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $request->apiKeyId);
        $stmt->bindValue(':ip', $request->ip);
        $stmt->bindValue(':event', $request->eventId);
        $stmt->bindValue(':error_type', $request->errorType);
        $stmt->bindValue(':error_text', $request->errorText);
        $stmt->bindValue(':raw', $request->raw);
        $stmt->bindValue(':started', $request->started);
        $stmt->execute();
    }
}
