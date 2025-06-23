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

namespace Controllers\Admin\Rules;

class Ruler extends \Controllers\Base {
    private $ruleBuilder;

    public function __construct() {
        $this->ruleBuilder = new \Ruler\RuleBuilder();
    }

    public function calculateByUid(string $uid, array $params): bool {
        $handler = "\\ExtendedRules\\" . $uid;

        if (!class_exists($handler)) {
            $handler = "\\Controllers\\Admin\\Rules\\Set\\" . $uid;
        }

        if (!class_exists($handler)) {
            return false;
        }

        $executed = false;

        try {
            $executed = (new $handler($this->ruleBuilder, $params))->execute();
        } catch (\Throwable $e) {
            // set validated false
            $model = new \Models\Rules();
            $model->setInvalidByUid($uid);

            error_log('Failed to execute rule ' . $uid . ': ' . $e->getMessage());
        }

        return $executed;
    }

    public function calculateByRule(Set\BaseRule $rule, array $params): bool {
        $executed = false;

        try {
            $rule->updateParams($params);
            $executed = $rule->execute();
        } catch (\Throwable $e) {
            if (defined($rule->uid)) {
                $model = new \Models\Rules();
                $model->setInvalidByUid($rule->uid);
            }

            error_log('Failed to execute rule class ' . $rule->uid . ': ' . $e->getMessage());
        }

        return $executed;
    }

    public function buildRuleObj(string $uid, array $params): ?Set\BaseRule {
        $handler = "\\ExtendedRules\\" . $uid;

        if (!class_exists($handler)) {
            $handler = "\\Controllers\\Admin\\Rules\\Set\\" . $uid;
        }

        if (!class_exists($handler)) {
            error_log('Failed to build rule ' . $uid . ': class does not exist.');

            return null;
        }

        try {
            return new $handler($this->ruleBuilder, $params);
        } catch (\Throwable $e) {
            error_log('Failed to build rule ' . $uid . ': ', $e->getMessage());
        }

        return null;
    }
}
