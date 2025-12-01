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

class ForgotPassword extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_operators_forgot_password';

    public function add(int $operatorId): void {
        $record = $this->getUnusedKeyByOperatorId($operatorId);

        if ($record) {
            $this->status = 'invalidated';
            $this->save();
        }

        $this->reset();
        $this->renew_key = $this->getPseudoRandomString(32);
        $this->operator_id = $operatorId;
        $this->status = 'unused';

        $this->save();
    }

    private function getUnusedKeyByOperatorId(int $operatorId): self|null|false {
        return $this->load(
            ['"operator_id"=? AND "status"=?', $operatorId, 'unused'],
        );
    }

    public function getUnusedByRenewKey(string $key): self|null|false {
        return $this->load(
            ['"renew_key"=? AND "status"=?', $key, 'unused'],
        );
    }

    public function deactivate(): void {
        if ($this->loaded()) {
            $this->status = 'used';

            $this->save();
        }
    }
}
