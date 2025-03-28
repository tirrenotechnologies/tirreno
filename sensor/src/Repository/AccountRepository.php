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

use Sensor\Dto\InsertAccountDto;
use Sensor\Entity\AccountEntity;
use Sensor\Model\Validated\Timestamp;

class AccountRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function checkExistence(AccountEntity $account): ?InsertAccountDto {
        $sql = 'SELECT id, session_id FROM event_account WHERE userid = :userid AND key = :key LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':userid', $account->userName);
        $stmt->bindValue(':key', $account->apiKeyId);
        $stmt->execute();

        /** @var array{id: int, session_id: int} $result */
        $result = $stmt->fetch();

        return $result !== false ? new InsertAccountDto($result['id'], null, null, $result['session_id']) : null;
    }

    public function insert(AccountEntity $account): InsertAccountDto {
        $sql = 'INSERT INTO event_account
                (userid, key, lastip, fullname, firstname, lastname, lastseen, updated, lastemail, lastphone, created, session_id)
            VALUES
                (:userid, :key, :lastip, :fullname, :firstname, :lastname, :lastseen, :updated,
                :lastemail, :lastphone, COALESCE(:created, CURRENT_TIMESTAMP), (SELECT nextval(\'session_id_seq\')))
            ON CONFLICT (key, userid) DO UPDATE
            SET
                lastip = EXCLUDED.lastip, lastseen = EXCLUDED.lastseen, firstname = COALESCE(EXCLUDED.firstname, event_account.firstname),
                fullname = COALESCE(EXCLUDED.fullname, event_account.fullname), lastname = COALESCE(EXCLUDED.lastname, event_account.lastname),
                created = COALESCE(:created, event_account.created),
                session_id = CASE
                    WHEN ABS(EXTRACT(epoch FROM (EXCLUDED.lastseen - event_account.lastseen))) > 1800
                        OR event_account.session_id IS NULL
                        OR (SELECT EXTRACT(epoch FROM (event_session.lastseen - event_session.created)) FROM event_session WHERE event_session.id = event_account.session_id LIMIT 1) > 14400
                    THEN nextval(\'session_id_seq\')
                    ELSE event_account.session_id END
            RETURNING id, lastemail, lastphone, session_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':userid', $account->userName);
        $stmt->bindValue(':key', $account->apiKeyId);
        $stmt->bindValue(':lastip', $account->lastIp);
        $stmt->bindValue(':fullname', $account->fullName);
        $stmt->bindValue(':firstname', $account->firstName);
        $stmt->bindValue(':lastname', $account->lastName);
        $stmt->bindValue(':lastseen', $account->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $account->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':lastemail', null);
        $stmt->bindValue(':lastphone', null);
        $stmt->bindValue(':created', $account->userCreated ? $account->userCreated->format(Timestamp::EVENTFORMAT) : null);
        $stmt->execute();

        /** @var array{id: int, lastemail: ?int, lastphone: ?int, session_id: int} $result */
        $result = $stmt->fetch();

        return new InsertAccountDto($result['id'], $result['lastemail'], $result['lastphone'], $result['session_id']);
    }

    public function updateLastEmailAndPhone(int $accountId, ?int $lastEmailId, ?int $lastPhoneId): void {
        $sql = 'UPDATE event_account
            SET lastemail = :lastemail, lastphone = :lastphone
            WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $accountId);
        $stmt->bindValue(':lastemail', $lastEmailId);
        $stmt->bindValue(':lastphone', $lastPhoneId);
        $stmt->execute();
    }
}
