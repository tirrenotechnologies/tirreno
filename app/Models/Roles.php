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

class Roles extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_roles';

    public function addRole(string $value, string $name): int {
        $params = [
            ':value'    => $value,
            ':name'     => $name,
        ];

        $query = (
            'INSERT INTO dshb_roles (
                value, name
            ) VALUES (
                :value, :name
            )
            RETURNING id'
        );

        return $this->execQuery($query, $params)[0]['id'];
    }

    public function getRoleById(int $roleId): array {
        $params = [
            ':role_id' => $roleId,
        ];

        $query = (
            'SELECT
                dshb_roles.id,
                dshb_roles.value,
                dshb_roles.name
            FROM
                dshb_roles
            WHERE
                dshb_roles.id = :role_id'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function getRoleByValue(string $value): array {
        $params = [
            ':value' => $value,
        ];

        $query = (
            'SELECT
                dshb_roles.id,
                dshb_roles.value,
                dshb_roles.name
            FROM
                dshb_roles
            WHERE
                dshb_roles.value = :value'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function removeRoleById(int $roleId): void {
        $params = [
            ':role_id' => $roleId,
        ];

        $query = (
            'DELETE FROM dshb_roles
            WHERE dshb_roles.id = :role_id'
        );

        $this->execQuery($query, $params);
    }

    public function getAllRolesWithPermissions(): array {
        $query = (
            'SELECT
                dshb_roles.id                       AS role_id,
                dshb_roles.value                    AS role_value,
                dshb_roles.name                     AS role_name,
                dshb_roles_permissions.permission   AS permission_id,
                dshb_permissions.value              AS permission_value,
                dshb_permissions.value              AS permission_name
            FROM
                dshb_roles

            LEFT JOIN dshb_roles_permissions
            ON dshb_roles.id = dshb_roles_permissions.role

            LEFT JOIN dshb_permissions
            ON dshb_roles_permissions.permission = dshb_permissions.id

            ORDER BY role ASC'
        );

        return $this->execQuery($query, null);
    }
}
