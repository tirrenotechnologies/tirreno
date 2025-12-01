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

class ApiKeyCoOwner extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_api_co_owners';

    public function getCoOwnership(int $operatorId): self|null|false {
        $filters = [
            'operator=?', $operatorId,
        ];

        return $this->load($filters);
    }

    public function getSharedApiKeyOperators(int $operatorId): array {
        $params = [
            ':creator' => $operatorId,
        ];

        $query = (
            'SELECT
                dshb_operators.id,
                dshb_operators.email,
                dshb_operators.is_active
            FROM
                dshb_api

            JOIN dshb_api_co_owners
            ON dshb_api.id = dshb_api_co_owners.api

            JOIN dshb_operators
            ON dshb_api_co_owners.operator = dshb_operators.id

            WHERE
                dshb_api.creator = :creator;'
        );

        return $this->execQuery($query, $params);
    }

    public function create(int $operator, int $api): void {
        $this->operator = $operator;
        $this->api = $api;

        $this->save();
    }

    public function deleteCoOwnership(): void {
        if ($this->loaded()) {
            $this->erase();
        }
    }
}
