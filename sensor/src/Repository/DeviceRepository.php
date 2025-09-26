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

use Sensor\Entity\DeviceEntity;
use Sensor\Dto\InsertUserAgentDto;
use Sensor\Model\Validated\Timestamp;

class DeviceRepository {
    public function __construct(
        private UserAgentRepository $userAgentRepository,
        private \PDO $pdo,
    ) {
    }


    // check for device for account with current ua
    /** @return array{id: int}|false Returns the fetched data or false if no data found */
    private function existingId(int $uaId, ?string $lang, int $accountId, int $key): array|false {
        $sql = '
            SELECT id
            FROM event_device
            WHERE
                key = :key AND
                account_id = :account_id AND
                user_agent = :ua_id AND
                (lang = :lang OR (lang IS NULL AND :lang IS NULL))
            FOR UPDATE LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $key);
        $stmt->bindValue(':account_id', $accountId);
        $stmt->bindValue(':ua_id', $uaId);
        $stmt->bindValue(':lang', $lang, $lang === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        $stmt->execute();

        /** @var array{id: int}|false $result */
        $result = $stmt->fetch();

        return $result;
    }

    public function insert(DeviceEntity $device): int {
        $uaDto = $this->userAgentRepository->insertSwitch($device->userAgent);

        $result = $this->existingId($uaDto->userAgentId, $device->lang, $device->accountId, $device->apiKeyId);

        // same device already exists
        if ($result !== false) {
            $sql = '
                UPDATE event_device
                SET
                    lastseen = :lastseen
                WHERE
                    id = :id AND key = :key';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':lastseen', $device->lastSeen->format(Timestamp::EVENTFORMAT));
            $stmt->bindValue(':id', $result['id']);
            $stmt->bindValue(':key', $device->apiKeyId);
            $stmt->execute();

            return $result['id'];
        }

        $sql = 'INSERT INTO event_device
                (account_id, key, user_agent, lang, lastseen, created, updated)
            VALUES
                (:account_id, :key, :user_agent, :lang, :lastseen, :created, :updated)
            ON CONFLICT (key, account_id, user_agent, lang) DO UPDATE
            SET
                lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':account_id', $device->accountId);
        $stmt->bindValue(':key', $device->apiKeyId);
        $stmt->bindValue(':user_agent', $uaDto->userAgentId);
        $stmt->bindValue(':lang', $device->lang, $device->lang === null ? \PDO::PARAM_NULL : \PDO::PARAM_STR);
        $stmt->bindValue(':lastseen', $device->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $device->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $device->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return $result['id'];
    }
}
