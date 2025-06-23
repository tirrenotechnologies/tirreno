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

class OperatorsRules extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_operators_rules';

    public function getAllValidRulesByOperator(int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value,
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
            $result[$row['uid']] = $row;
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
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value,
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

    public function getRuleWithOperatorValue(string $ruleUid, int $apiKey): array {
        $params = [
            ':api_key'  => $apiKey,
            ':uid'       => $ruleUid,
        ];

        $query = (
            'SELECT
                dshb_rules.uid,
                dshb_rules.validated,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.attributes,
                COALESCE(dshb_operators_rules.value, 0) AS value

            FROM
                dshb_rules

            LEFT JOIN dshb_operators_rules
            ON (dshb_rules.uid = dshb_operators_rules.rule_uid AND dshb_operators_rules.key = :api_key)

            WHERE
                dshb_rules.uid = :uid AND
                dshb_rules.missing IS NOT TRUE AND
                dshb_rules.validated IS TRUE'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function updateRule(string $ruleUid, int $score, int $apiKey): void {
        $found = $this->load(
            ['"key"=? AND "rule_uid"=?', $apiKey, $ruleUid],
        );

        if (!$found) {
            $this->key = $apiKey;
            $this->rule_uid = $ruleUid;
            $this->proportion = null;
        }

        $this->value = $score;
        // do not change proportion

        $this->save();
    }

    public function updateRuleProportion(string $ruleUid, float $proportion, int $apiKey): void {
        $found = $this->load(
            ['"key"=? AND "rule_uid"=?', $apiKey, $ruleUid],
        );

        // set value if record is new
        if (!$found) {
            $this->key = $apiKey;
            $this->rule_uid = $ruleUid;
            $this->value = 0;
        }

        $this->proportion = $proportion;
        $this->proportion_updated_at = date('Y-m-d H:i:s');

        $this->save();
    }
}
