<?php

/**
 * tirreno ~ open-source security framework
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

namespace Tirreno\Models;

class Watchlist extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_account';

    public function add(int $accountId, int $apiKey): void {
        $params = [
            ':account_id'   => $accountId,
            ':api_key'      => $apiKey,
        ];

        $query = (
            'UPDATE event_account
            SET
                is_important = 1
            WHERE
                event_account.id = :account_id AND
                event_account.key = :api_key'
        );

        $this->execQuery($query, $params);
    }

    public function remove(int $accountId, int $apiKey): void {
        $params = [
            ':account_id'   => $accountId,
            ':api_key'      => $apiKey,
        ];

        $query = (
            'UPDATE event_account
            SET
                is_important = 0
            WHERE
                event_account.id = :account_id AND
                event_account.key = :api_key'
        );

        $this->execQuery($query, $params);
    }

    public function getUsersByKey(int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                id,
                userid,
                created,
                lastseen
            FROM
                event_account
            WHERE
                event_account.is_important = 1 AND
                event_account.key = :api_key'
        );

        return $this->execQuery($query, $params);
    }
}
