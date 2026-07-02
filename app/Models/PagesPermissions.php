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

class PagesPermissions extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_pages_permissions';

    public function addPagePermission(int $rolePermission, int $page): int {
        $params = [
            ':page'             => $page,
            ':role_permission'  => $rolePermission,
        ];

        $query = (
            'INSERT INTO dshb_pages_permissions (
                page, role_permission
            ) VALUES (
                :page, :role_permission
            )
            RETURNING id'
        );

        return $this->execQuery($query, $params)[0]['id'];
    }

    public function getByPagePermissionId(int $pagePermissionId): array {
        $params = [
            ':page_permission_id' => $pagePermissionId,
        ];

        $query = (
            'SELECT
                dshb_pages_permissions.id,
                dshb_pages_permissions.page,
                dshb_pages_permissions.role_permission
            FROM
                dshb_pages_permissions
            WHERE
                dshb_pages_permissions.id = :page_permission_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getByRolePermissionId(int $rolePermissionId): array {
        $params = [
            ':role_permission_id' => $rolePermissionId,
        ];

        $query = (
            'SELECT
                dshb_pages_permissions.id,
                dshb_pages_permissions.page,
                dshb_pages_permissions.role_permission
            FROM
                dshb_pages_permissions
            WHERE
                dshb_pages_permissions.role_permission = :role_permission_id'
        );

        return $this->execQuery($query, $params);
    }

    public function getRolePermissionsByPageId(int $pageId): array {
        $params = [
            ':page_id' => $pageId,
        ];

        $query = (
            'SELECT
                dshb_roles_permissions.id,
                dshb_roles_permissions.page,
                dshb_roles_permissions.role_permission
            FROM
                dshb_roles_permissions
            WHERE
                dshb_roles_permissions.page = :page_id'
        );

        return $this->execQuery($query, $params);
    }

    public function removePagePermissionById(int $pagePermissionId): void {
        $params = [
            ':page_permission_id' => $pagePermissionId,
        ];

        $query = (
            'DELETE FROM dshb_pages_permissions
            WHERE dshb_pages_permissions.id = :page_permission_id'
        );

        $this->execQuery($query, $params);
    }
}
