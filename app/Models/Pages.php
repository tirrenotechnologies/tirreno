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

class Pages extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_pages';

    public function addPage(string $value, string $name): int {
        $params = [
            ':value'    => $value,
            ':name'     => $name,
        ];

        $query = (
            'INSERT INTO dshb_pages (
                value, name
            ) VALUES (
                :value, :name
            )
            RETURNING id'
        );

        return $this->execQuery($query, $params)[0]['id'];
    }

    public function getPageById(int $pageId): array {
        $params = [
            ':page_id' => $pageId,
        ];

        $query = (
            'SELECT
                dshb_pages.id,
                dshb_pages.value,
                dshb_pages.name
            FROM
                dshb_pages
            WHERE
                dshb_pages.id = :page_id'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function getPageByValue(string $value): array {
        $params = [
            ':value' => $value,
        ];

        $query = (
            'SELECT
                dshb_pages.id,
                dshb_pages.value,
                dshb_pages.name
            FROM
                dshb_pages
            WHERE
                dshb_pages.value = :value'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function getPageByName(string $name): array {
        $params = [
            ':name' => $name,
        ];

        $query = (
            'SELECT
                dshb_pages.id,
                dshb_pages.value,
                dshb_pages.name
            FROM
                dshb_pages
            WHERE
                dshb_pages.name = :name'
        );

        return $this->execQuery($query, $params)[0] ?? [];
    }

    public function removePageById(int $pageId): void {
        $params = [
            ':page_id' => $pageId,
        ];

        $query = (
            'DELETE FROM dshb_pages
            WHERE dshb_pages.id = :page_id'
        );

        $this->execQuery($query, $params);
    }
}
