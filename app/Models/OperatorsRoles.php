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

class OperatorsRoles extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_operators_roles';

    public function addOperatorRole(int $role, int $operator): int {
        $params = [
            ':role'     => $role,
            ':operator'  => $operator,
        ];

        $query = (
            'INSERT INTO dshb_operators_roles (
                role, operator
            ) VALUES (
                :role, :operator
            ) ON CONFLICT DO NOTHING
            RETURNING id'
        );

        return $this->execQuery($query, $params)[0]['id'];
    }

    public function getByOperatorRoleId(int $operatorRoleId): array {
        $params = [
            ':operator_role_id' => $operatorRoleId,
        ];

        $query = (
            'SELECT
                dshb_operators_roles.id,
                dshb_operators_roles.operator,
                dshb_operators_roles.role
            FROM
                dshb_operators_roles
            WHERE
                dshb_operators_roles.id = :operator_role_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getOperatorRole(int $roleId, int $operatorId): array {
        $params = [
            ':operator_id'  => $operatorId,
            ':role_id'      => $roleId,
        ];

        $query = (
            'SELECT
                dshb_operators_roles.id,
                dshb_operators_roles.operator,
                dshb_operators_roles.role
            FROM
                dshb_operators_roles
            WHERE
                dshb_operators_roles.operator = :operator_id AND
                dshb_operators_roles.roel = :role_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getRoleByOperatorId(int $operatorId): array {
        $params = [
            ':operator_id' => $operatorId,
        ];

        $query = (
            'SELECT
                dshb_operators_roles.id,
                dshb_operators_roles.operator,
                dshb_operators_roles.role
            FROM
                dshb_operators_roles
            WHERE
                dshb_operators_roles.operator = :operator_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getOperatorByRoleId(int $roleId): array {
        $params = [
            ':role_id' => $roleId,
        ];

        $query = (
            'SELECT
                dshb_operators_roles.id,
                dshb_operators_roles.page,
                dshb_operators_roles.role_permission
            FROM
                dshb_operators_roles
            WHERE
                dshb_operators_roles.role = :role_id'
        );

        return $this->execQuery($query, $params);
    }

    public function checkPagePermission(string $permissionValue, string $pageName, int $operatorId): bool {
        $params = [
            ':operator'         => $operatorId,
            ':page_name'        => $pageName,
            ':permission_value' => $permissionValue,
        ];

        $query = (
            'SELECT
                dshb_pages_permissions.id

            FROM
                dshb_operators_roles

            LEFT JOIN dshb_roles_permissions
            ON dshb_roles_permissions.role = dshb_operators_roles.role

            LEFT JOIN dshb_permissions
            ON dshb_permissions.id = dshb_roles_permissions.permission

            LEFT JOIN dshb_pages_permissions
            ON dshb_pages_permissions.role_permission = dshb_roles_permissions.id

            LEFT JOIN dshb_pages
            ON dshb_pages.id = dshb_pages_permissions.page

            WHERE
                dshb_pages.name = :page_name AND
                dshb_permissions.value = :permission_value AND
                dshb_operators_roles.operator = :operator

            LIMIT 1'
        );

        return boolval(count($this->execQuery($query, $params)));
    }

    public function getRolesWithPermissions(int $operatorId): array {
        $params = [
            ':operator' => $operatorId,
        ];

        $query = (
            'SELECT
                dshb_operators_roles.role,
                dshb_operators_roles.operator,
                dshb_roles.value        AS role_value,
                dshb_roles.name         AS role_name,
                dshb_roles_permissions.permission,
                dshb_permissions.value  AS permission_value,
                dshb_permissions.value  AS permission_name
            FROM
                dshb_operators_roles

            LEFT JOIN dshb_roles_permissions
            ON dshb_operators_roles.role = dshb_roles_permissions.role

            LEFT JOIN dshb_permissions
            ON dshb_roles_permissions.permission = dshb_permissions.id

            LEFT JOIN dshb_roles
            ON dshb_roles.id = dshb_operators_roles.role

            WHERE
                dshb_operators_roles.operator = :operator
            ORDER BY role ASC'
        );

        return $this->execQuery($query, $params);
    }


    public function removeOperatorRoleById(int $operatorRoleId): void {
        $params = [
            ':operator_role_id' => $operatorRoleId,
        ];

        $query = (
            'DELETE FROM dshb_operators_roles
            WHERE dshb_operators_roles.id = :operator_role_id'
        );

        $this->execQuery($query, $params);
    }

    public function removeOperatorRole(int $roleId, int $operatorId): void {
        $params = [
            ':operator_id' => $operatorId,
            ':role_id' => $roleId,
        ];

        $query = (
            'DELETE FROM dshb_operators_roles
            WHERE
                dshb_operators_roles.operator = :operator_id AND
                dshb_operators_roles.role = :role_id'
        );

        $this->execQuery($query, $params);
    }


///////////////////
/*
    public function addRoleById(int $role, int $operatorId): void {
        $params = [
            ':operator' => $operatorId,
            ':role'     => $role,
        ];

        $query = (
            'INSERT INTO dshb_operators_roles (
                operator, role
            ) VALUES (
                :operator, :role
            )
            ON CONFLICT (operator, role) DO NOTHING'
        );

        $this->execQuery($query, $params);
    }

    public function removeRoleById(int $role, int $operatorId): void {
        $params = [
            ':operator' => $operatorId,
            ':role'     => $role,
        ];

        $query = (
            'DELETE FROM dshb_operators_roles
            WHERE
                operator = :operator AND
                role = :role'
        );

        $this->execQuery($query, $params);
    }

    public function addRole(string $role, int $operatorId): void {
        $params = [
            ':operator' => $operatorId,
            ':role'     => $role,
        ];

        $query = (
            'INSERT INTO dshb_operators_roles (
                operator, role
            ) VALUES (
                :operator, :role
            )
            ON CONFLICT (operator, role) DO NOTHING'
        );

        $this->execQuery($query, $params);
    }

    public function removeRole(string $role, int $operatorId): void {
        $params = [
            ':operator' => $operatorId,
            ':role'     => $role,
        ];

        $query = (
            'DELETE FROM dshb_operators_roles
            WHERE
                operator = :operator AND
                role = :role'
        );

        $this->execQuery($query, $params);
    }

    public function hasRole(string $role, int $operatorId): bool {
        $params = [
            ':operator' => $operatorId,
            ':role'     => $role,
        ];

        $query = (
            'SELECT
                1
            FROM
                dshb_operators_roles
            WHERE
                operator = :operator AND
                role = :role
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return !empty($results);
    }

    public function getRoles(int $operatorId): array {
        $params = [
            ':operator' => $operatorId,
        ];

        $query = (
            'SELECT
                role
            FROM
                dshb_operators_roles
            WHERE
                operator = :operator
            ORDER BY role ASC'
        );

        $rows = $this->execQuery($query, $params);

        $roles = array_filter(
            array_column($rows, 'role'),
            static function ($value): bool {
                $result = is_string($value) && $value !== '';
                return $result;
            }
        );

        return $roles;
    }

    public function getRolesWithPermissions(int $operatorId): array {
        $params = [
            ':operator' => $operatorId,
        ];

        $query = (
            'SELECT
                dshb_operators_roles.role,
                dshb_roles.value        AS role_value,
                dshb_roles.name         AS role_name,
                dshb_roles_permissions.permission,
                dshb_permissions.value  AS permission_value,
                dshb_permissions.value  AS permission_name
            FROM
                dshb_operators_roles

            LEFT JOIN dshb_roles_permissions
            ON dshb_operators_roles.role = dshb_roles_permissions.role

            LEFT JOIN dshb_permissions
            ON dshb_roles_permissions.permission = dshb_permissions.id

            LEFT JOIN dshb_roles
            ON dshb_roles.id = dshb_operators_roles.role

            WHERE
                dshb_operators_roles.operator = :operator
            ORDER BY role ASC'
        );

        return $this->execQuery($query, $params);
    }
*/
}
