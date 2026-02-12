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

class Log extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_logs';

    public function insertRecord(array $data): void {
        $params = [
            ':text' => json_encode($data),
        ];

        $query = (
            'INSERT INTO dshb_logs (text) VALUES (:text)'
        );

        $this->execQuery($query, $params);
    }
}
