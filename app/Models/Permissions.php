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

class Permissions extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_permissions';

    public function addPermission(string $value, string $name): int {
        $params = [
            ':value'    => $value,
            ':name'     => $name,
        ];

        $query = (
            'INSERT INTO dshb_permissions (
                value, name
            ) VALUES (
                :value, :name
            )
            RETURNING id'
        );

        return $this->execQuery($query, $params)[0]['id'];
    }

    public function getPermissionById(int $permissionId): array {
        $params = [
            ':permission_id' => $permissionId,
        ];

        $query = (
            'SELECT
                dshb_permissions.id,
                dshb_permissions.value,
                dshb_permissions.name
            FROM
                dshb_permissions
            WHERE
                dshb_permissions.id = :role_id'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function getPermissionByValue(string $value): array {
        $params = [
            ':value' => $value,
        ];

        $query = (
            'SELECT
                dshb_permissions.id,
                dshb_permissions.value,
                dshb_permissions.name
            FROM
                dshb_permissions
            WHERE
                dshb_permissions.value = :value'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function removePermissionById(int $permissionId): void {
        $params = [
            ':permission_id' => $permissionId,
        ];

        $query = (
            'DELETE FROM dshb_permissions
            WHERE dshb_permissions.id = :permission_id'
        );

        $this->execQuery($query, $params);
    }
}
