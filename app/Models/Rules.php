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

class Rules extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_rules';

    public function getAll(): array {
        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                dshb_rules.missing

            FROM
                dshb_rules'
        );

        return $this->execQuery($query, null);
    }

    public function addRule(string $uid, string $name, string $descr, array $attr, bool $validated): void {
        $params = [
            ':validated'    => $validated,
            ':uid'          => $uid,
            ':name'         => $name,
            ':descr'        => $descr,
            ':attributes'   => json_encode($attr),
        ];

        $query = (
            'INSERT INTO dshb_rules (uid, name, descr, validated, attributes)
            VALUES (:uid, :name, :descr, :validated, :attributes)
            ON CONFLICT (uid) DO UPDATE
            SET name = EXCLUDED.name, descr = EXCLUDED.descr, validated = EXCLUDED.validated,
            attributes = EXCLUDED.attributes, updated = now(), missing = null'
        );

        $this->execQuery($query, $params);
    }

    public function setInvalidByUid(string $uid): void {
        $params = [
            ':uid'   => $uid,
        ];

        $query = (
            'UPDATE dshb_rules
            SET validated = false, updated = now()
            WHERE dshb_rules.uid = :uid'
        );

        $this->execQuery($query, $params);
    }

    public function setMissingByUid(string $uid): void {
        $params = [
            ':uid'   => $uid,
        ];

        $query = (
            'UPDATE dshb_rules
            SET missing = true, updated = now()
            WHERE dshb_rules.uid = :uid'
        );

        $this->execQuery($query, $params);
    }

    public function deleteByUid(string $uid): void {
        $params = [
            ':uid'   => $uid,
        ];

        $query = (
            'DELETE FROM dshb_rules WHERE uid = :uid'
        );

        $this->execQuery($query, $params);
    }
}
