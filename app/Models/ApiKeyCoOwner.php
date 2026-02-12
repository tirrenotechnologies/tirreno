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

class ApiKeyCoOwner extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_api_co_owners';

    public function getCoOwnershipKeyId(int $operatorId): ?int {
        $params = [
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'SELECT
                api AS key
            FROM
                dshb_api_co_owners
            WHERE
                dshb_api_co_owners.operator = :operator_id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['key'] ?? null;
    }

    public function insertRecord(int $operatorId, int $apiKey): void {
        $params = [
            ':operator_id'  => $operatorId,
            ':api_key'      => $apiKey,
        ];

        $query = (
            'INSERT INTO dshb_api_co_owners (operator, api)
            VALUES (:operator_id, :api_key)'
        );

        $this->execQuery($query, $params);
    }

    public function deleteCoOwnership(int $operatorId): void {
        $params = [
            ':operator_id' => $operatorId,
        ];

        $query = (
            'DELETE FROM dshb_api_co_owners
            WHERE operator = :operator_id'
        );

        $this->execQuery($query, $params);
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
}
