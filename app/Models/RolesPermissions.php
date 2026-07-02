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

class RolesPermissions extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_roles_permissions';

    public function addRolePermission(int $permission, int $role): int {
        $params = [
            ':role'         => $role,
            ':permission'   => $permission,
        ];

        $query = (
            'INSERT INTO dshb_roles_permissions (
                role, permission
            ) VALUES (
                :role, :permission
            )
            RETURNING id'
        );

        return $this->execQuery($query, $params)[0]['id'];
    }

    public function getByRolePermissionId(int $rolePermissionId): array {
        $params = [
            ':role_permission_id' => $rolePermissionId,
        ];

        $query = (
            'SELECT
                dshb_roles_permissions.id,
                dshb_roles_permissions.role,
                dshb_roles_permissions.permission
            FROM
                dshb_roles_permissions
            WHERE
                dshb_roles_permissions.id = :role_permission_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getPermissionsByRoleId(int $roleId): array {
        $params = [
            ':role_id' => $roleId,
        ];

        $query = (
            'SELECT
                dshb_roles_permissions.id,
                dshb_roles_permissions.role,
                dshb_roles_permissions.permission
            FROM
                dshb_roles_permissions
            WHERE
                dshb_roles_permissions.role = :role_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getRolesByPermissionId(int $permissionId): array {
        $params = [
            ':permission_id' => $permissionId,
        ];

        $query = (
            'SELECT
                dshb_roles_permissions.id,
                dshb_roles_permissions.role,
                dshb_roles_permissions.permission
            FROM
                dshb_roles_permissions
            WHERE
                dshb_roles_permissions.permissions = :permission_id'
        );

        return $this->execQuery($query, $params);
    }

    public function removeRolePermissionById(int $rolePermissionId): void {
        $params = [
            ':role_permission_id' => $rolePermissionId,
        ];

        $query = (
            'DELETE FROM dshb_roles_permissions
            WHERE dshb_roles_permissions.id = :role_permission_id'
        );

        $this->execQuery($query, $params);
    }
}
