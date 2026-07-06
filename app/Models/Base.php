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

abstract class Base {
    protected string $tableName = '';

    protected function getDatabaseConnection(): ?\DB\SQL {
        return tirreno('utils')->database->getDb();
    }

    public function printLog(): void {
        echo $this->getDatabaseConnection()->log();
    }

    public function getArrayPlaceholders(array $ids, string $postfix = ''): array {
        $params = [];
        $placeHolders = [];

        $postfix = $postfix !== '' ? '_' . $postfix : '';

        foreach ($ids as $i => $id) {
            $key = sprintf(':item_id_%s%s', $i, $postfix);
            $placeHolders[] = $key;
            $params[$key] = $id;
        }

        $placeHolders = implode(', ', $placeHolders);

        return [$params, $placeHolders];
    }

    public function execQuery(string $query, ?array $params): array|int|null {
        return $this->getDatabaseConnection()->exec($query, $params);
    }

    public function tableExists(?string $tableName = null): bool {
        if (!$this->tableName && !$tableName) {
            return false;
        }

        $params = [
            ':tableName'    => $tableName ? $tableName : $this->tableName,
        ];
        $query = (
            'SELECT to_regclass(:tableName) IS NOT NULL AS exists'
        );

        return boolval($this->execQuery($query, $params)[0]['exists'] ?? false);
    }
}
