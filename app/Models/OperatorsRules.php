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

class OperatorsRules extends \Tirreno\Models\Base {
    protected string $tableName = 'dshb_operators_rules';

    public function getAllValidRulesByOperator(int $apiKey): array {
        $params = [
            ':api_key'          => $apiKey,
            ':primary_rule_set' => tirreno('utils')->constants->PRIMARY_RULES_SET_ID,
        ];

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value,
                COALESCE(dshb_operators_rules.set, :primary_rule_set) AS set,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key)

            WHERE
                dshb_rules.missing IS NOT TRUE AND
                dshb_rules.validated IS TRUE'
        );

        $results = $this->execQuery($query, $params);

        $result = [];
        foreach ($results as $row) {
            if (!isset($result[$row['set']])) {
                $result[$row['set']] = [];
            }

            $result[$row['set']][$row['uid']] = $row;
        }

        // attributes filter applied in controller
        return $result;
    }

    public function getAllRulesByOperator(int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.missing,
                (NOT COALESCE(dshb_rules.validated, FALSE) OR COALESCE(dshb_rules.missing, FALSE)) AS broken,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value,
                dshb_operators_rules.set,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key)'
        );

        $results = $this->execQuery($query, $params);

        $result = [];
        foreach ($results as $row) {
            $result[$row['uid']] = $row;
        }

        return $result;
    }

    public function getAllRulesByOperatorAndSet(int $setId, int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
            ':set_id'   => $setId,
        ];

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.missing,
                (NOT COALESCE(dshb_rules.validated, FALSE) OR COALESCE(dshb_rules.missing, FALSE)) AS broken,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value,
                COALESCE(dshb_operators_rules.set, :set_id) AS set,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key AND dshb_operators_rules.set = :set_id)'
        );

        $results = $this->execQuery($query, $params);

        $result = [];
        foreach ($results as $row) {
            $result[$row['uid']] = $row;
        }

        return $result;
    }

    public function getRuleWithOperatorValue(string $ruleUid, int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
            ':uid'      => $ruleUid,
            ':set_id'   => tirreno('utils')->constants->PRIMARY_RULES_SET_ID,
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
                dshb_operators_rules.set,
                dshb_operators_rules.value,
                dshb_operators_rules.id,
                dshb_operators_rules.created_at,
                dshb_operators_rules.proportion,
                dshb_operators_rules.proportion_updated_at

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key AND dshb_operators_rules.set = :set_id)

            WHERE
                dshb_rules.uid = :uid AND
                dshb_rules.missing IS NOT TRUE AND
                dshb_rules.validated IS TRUE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function updateRule(string $ruleUid, int $score, int $apiKey): void {
        $params = [
            ':score'    => $score,
            ':uid'      => $ruleUid,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'INSERT INTO dshb_operators_rules (
                key, rule_uid, value
            ) VALUES (
                :api_key, :uid, :score
            ) ON CONFLICT (key, rule_uid, set) DO UPDATE SET
                value = EXCLUDED.value'
        );

        $this->execQuery($query, $params);
    }

    // update for all sets
    public function updateRuleProportion(string $ruleUid, float $proportion, int $apiKey): void {
        $params = [
            ':proportion'   => $proportion,
            ':uid'          => $ruleUid,
            ':api_key'      => $apiKey,
        ];

        $query = (
            'INSERT INTO dshb_operators_rules (
                key, rule_uid, proportion, proportion_updated_at, value
            ) VALUES (
                :api_key, :uid, :proportion, NOW(), 0
            ) ON CONFLICT (key, rule_uid, set) DO UPDATE SET
                proportion = EXCLUDED.proportion, proportion_updated_at = NOW()'
        );

        $this->execQuery($query, $params);

        $query = (
            'UPDATE dshb_operators_rules
            SET
                proportion = :proportion,
                proportion_updated_at = NOW()
            WHERE
                dshb_operators_rules.rule_uid = :uid AND
                dshb_operators_rules.key = :api_key'
        );

        $this->execQuery($query, $params);
    }

    public function isSetActive(int $setId, int $apiKey): bool {
        $params = [
            ':set_id'   => $setId,
            ':api_key'  => $apiKey,
            ':inactive' => 0,
        ];

        $query = (
            'SELECT COUNT(id)
            FROM dshb_operators_rules
            WHERE
                dshb_operators_rules.key = :api_key AND
                dshb_operators_rules.set = :set_id AND
                dshb_operators_rules.value != :inactive'
        );

        return boolval($this->execQuery($query, $params)['count']);
    }

    public function getActiveSets(int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
            ':inactive' => 0,
        ];

        $query = (
            'SELECT DISTINCT set
            FROM dshb_operators_rules
            WHERE
                dshb_operators_rules.key = :api_key AND
                dshb_operators_rules.value != :inactive'
        );

        $result = array_column($this->execQuery($query, $params), 'set');
        // have at least one set
        $primaryRulesSetId = tirreno('utils')->constants->PRIMARY_RULES_SET_ID;
        if (!in_array($primaryRulesSetId, $result, true)) {
            $result[] = $primaryRulesSetId;
        }

        return $result;
    }
}
