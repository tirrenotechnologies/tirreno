<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Models;

abstract class BaseSql extends \DB\SQL\Mapper {
    use \Traits\Debug;

    protected $f3 = null;
    protected $DB_TABLE_TTL = 0;
    protected $DB_TABLE_NAME = null;
    protected $DB_TABLE_FIELDS = null;

    public function __construct() {
        $this->f3 = \Base::instance();

        if ($this->DB_TABLE_NAME) {
            $DB = $this->getDatabaseConnection();
            parent::__construct($DB, $this->DB_TABLE_NAME, $this->DB_TABLE_FIELDS, $this->DB_TABLE_TTL);
        }
    }

    private function getDatabaseConnection(): ?\DB\SQL {
        return $this->f3->get('API_DATABASE');
    }

    public function getHash(string $string): string {
        $iterations = 1000;
        $salt = $this->f3->get('SALT');

        return hash_pbkdf2('sha256', $string, $salt, $iterations, 32);
    }

    public function getPseudoRandomString(int $length = 32): string {
        $bytes = \openssl_random_pseudo_bytes($length / 2);

        return \bin2hex($bytes);
    }

    public function printLog(): void {
        echo $this->f3->get('API_DATABASE')->log();
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
}
