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

namespace Models;

class Watchlist extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_account';

    public function add(int $accountId, int $apiKey): void {
        $this->getById($accountId, $apiKey);

        if ($this->loaded()) {
            $this->is_important = 1;
            $this->save();
        }
    }

    public function remove(int $accountId, int $apiKey): void {
        $this->getById($accountId, $apiKey);

        if ($this->loaded()) {
            $this->is_important = 0;
            $this->save();
        }
    }

    private function getById(int $accountId, int $apiKey): void {
        $this->load(
            ['id=? AND key=?', $accountId, $apiKey],
        );
    }

    public function getUsersByKey(int $apiKey): array {
        return $this->find(
            ['key=? AND is_important=?', $apiKey, 1],
        );
    }
}
