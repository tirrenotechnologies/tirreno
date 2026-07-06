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

class Rules extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_rules';

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

    // collect rule values from dshb_rules, not from stored user-related score_details
    public function getRulesByUserId(int $userId, int $apiKey): array {
        $params = [
            ':user_id'  => $userId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            "SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                dshb_rules.updated,
                dshb_rules.missing,
                dshb_operators_rules.value,
                dshb_operators_rules.id,
                dshb_operators_rules.created_at,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                event_account

            CROSS JOIN jsonb_array_elements(event_account.score_details) AS details

            LEFT JOIN dshb_rules
            ON dshb_rules.uid = details->>'uid'

            LEFT JOIN dshb_operators_rules
            ON dshb_operators_rules.rule_uid = details->>'uid' AND dshb_operators_rules.key = event_account.key

            WHERE
                event_account.id = :user_id AND
                event_account.key = :api_key"

        );

        return $this->execQuery($query, $params);
    }

    public function getRulesByOperator(int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                dshb_rules.updated,
                dshb_rules.missing,
                dshb_operators_rules.value,
                dshb_operators_rules.id,
                dshb_operators_rules.created_at,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key)'
        );

        return $this->execQuery($query, $params);
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
