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

namespace Tirreno\Utils;

class OperatorAccess {
    public static function addOperatorRole(string $role, int $operatorId): void {
        $roleId = tirreno('models')->roles->getRoleByValues($role)['id'] ?? null;
        if (!$roleId) {
            return;
        }

        self::rolesModel()->addOperatorRole($roleId, $operatorId);
    }

    public static function removeOperatorRole(string $role, int $operatorId): void {
        $roleId = tirreno('models')->roles->getRoleByValues($role)['id'] ?? null;
        if (!$roleId) {
            return;
        }

        self::rolesModel()->removeOperatorRole($roleId, $operatorId);
    }

    public static function addOperatorRoleById(int $role, int $operatorId): void {
        self::rolesModel()->addOperatorRole($role, $operatorId);
    }

    public static function removeOperatorRoleById(int $role, int $operatorId): void {
        self::rolesModel()->removeOperatorRole($role, $operatorId);
    }

    public static function operatorHasRole(string $role, int $operatorId): bool {
        $roleId = tirreno('models')->roles->getRoleByValues($role)['id'] ?? null;

        return $roleId ? boolval(count(self::rolesModel()->getOperatorRole($roleId, $operatorId))) : false;
    }

    public static function getRoles(int $operatorId): array {
        $roles = self::rolesModel()->getRolesWithPermissions($operatorId);

        $result = [];
        foreach ($roles as $role) {
            $roleValue = $role['role_value'];
            if (!isset($result[$roleValue])) {
                $result[$roleValue] = [
                    'id'            => $role['role'],
                    'value'         => $role['role_value'],
                    'name'          => $role['role_name'],
                    'permissions'   => [],
                ];
            }

            $permissionValue = $role['permission_value'];
            if (!isset($result[$roleValue]['permissions'][$permissionValue])) {
                $result[$roleValue]['permissions'][$permissionValue] = [
                    'id'    => $role['permission'],
                    'value' => $role['permission_value'],
                    'name'  => $role['permission_name'],
                ];
            }
        }

        return $result;
    }

    public static function getRolesWithPermissions(int $operatorId): array {
        $roles = self::rolesModel()->getRolesWithPermissions($operatorId);

        $permissions = [];

        foreach ($roles as $role) {
            if (!isset($permissions[$role['role_value']])) {
                $permissions[$role['role_value']] = [];
            }

            $permissions[$role['role_value']][] = $role;
        }

        return $permissions;
    }

    public static function hasPermission(int $permission, int $operatorId): bool {
        $userRoles = self::getRolesWithPermissions($operatorId);
        $permissionIds = array_column(array_merge(...array_values($userRoles)), 'permission');

        return in_array($permission, $permissionIds);
    }

    public static function getPermissions(): array {
        $permissions = tirreno('models')->roles->getAllRolesWithPermissions();

        $result = [];

        foreach ($permissions as $permission) {
            $roleId = $permission['role_id'];
            if (!isset($result[$roleId])) {
                $result[$roleId] = [
                    'permissions'   => [],
                    'role_name'     => $permission['role_name'],
                    'role_value'    => $permission['role_value'],
                ];
            }

            $permissionId = $permission['permission_id'];
            $result[$roleId]['permissions'][$permissionId] = [
                'permission_name'   => $permission['permission_name'],
                'permission_value'  => $permission['permission_value'],
            ];
        }

        return $result;
    }

    public static function viewable(string $page, int $operatorId): bool {
        return self::rolesModel()->checkPagePermission('page_view', $page, $operatorId);
    }

    public static function editable(string $page, int $operatorId): bool {
        return self::rolesModel()->checkPagePermission('page_edit', $page, $operatorId);
    }

    public static function deleteable(string $page, int $operatorId): bool {
        return self::rolesModel()->checkPagePermission('page_delete', $page, $operatorId);
    }

    public static function publishable(string $page, int $operatorId): bool {
        return self::rolesModel()->checkPagePermission('page_publish', $page, $operatorId);
    }

    public static function adminable(string $page, int $operatorId): bool {
        return self::rolesModel()->checkPagePermission('user_admin', $page, $operatorId);
    }

    private static function rolesModel(): \Tirreno\Models\OperatorsRoles {
        return tirreno('models')->operatorsRoles;
    }
}
