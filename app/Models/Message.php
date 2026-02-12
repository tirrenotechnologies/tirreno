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

class Message extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_message';

    public function addMessage(string $msg): int {
        $params = [
            ':text' => $msg,
        ];

        $query = (
            'INSERT INTO dshb_message (text) VALUES (:text) RETURNING id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'];
    }

    public function getLastMessage(): array {
        $query = (
            'SELECT
                id,
                text,
                title,
                created_at
            FROM
                dshb_message
            ORDER BY id DESC
            LIMIT 1'
        );

        $results = $this->execQuery($query, null);

        return $results[0] ?? [];
    }
}
