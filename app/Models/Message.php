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

class Message extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_message';

    public function addMessage(string $msg): int {
        $this->text = $msg;

        $this->save();

        return \Utils\Conversion::intVal($this->id, 0);
    }

    public function getMessage(): self|null|false {
        $filters = [];
        $options = [
            'order' => 'id DESC',
            'offset' => 0,
            'limit' => 1,
        ];

        return $this->load($filters, $options);
    }
}
