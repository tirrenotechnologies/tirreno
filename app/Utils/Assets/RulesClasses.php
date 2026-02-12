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

namespace Tirreno\Utils\Assets;

class RulesClasses extends Base {
    private const RULE_BROKEN = 'broken';
    private const RULES_WEIGHT = [
        -20 =>  'positive',
        10 =>   'medium',
        20 =>   'high',
        70 =>   'extreme',
        0 =>    'none',
    ];

    private const RULES_TYPES = [
        'A' => 'Account takeover',
        'B' => 'Behaviour',
        'C' => 'Country',
        'D' => 'Device',
        'E' => 'Email',
        'I' => 'IP',
        'R' => 'Reuse',
        'P' => 'Phone',
        'X' => 'Extra',
    ];

    public static function getRuleClass(?int $value, bool $broken): string {
        return $broken ? self::RULE_BROKEN : self::RULES_WEIGHT[$value ?? 0] ?? 'none';
    }

    public static function getRuleTypeByUid(string $uid): string {
        return self::RULES_TYPES[$uid[0]] ?? $uid[0];
    }

    public static function getUserScoreClass(?int $score): array {
        $cls = 'empty';
        if ($score === null) {
            return ['&minus;', $cls];
        }

        if ($score >= \Tirreno\Utils\Constants::get()->USER_LOW_SCORE_INF && $score < \Tirreno\Utils\Constants::get()->USER_LOW_SCORE_SUP) {
            $cls = 'low';
        }

        if ($score >= \Tirreno\Utils\Constants::get()->USER_MEDIUM_SCORE_INF && $score < \Tirreno\Utils\Constants::get()->USER_MEDIUM_SCORE_SUP) {
            $cls = 'medium';
        }

        if ($score >= \Tirreno\Utils\Constants::get()->USER_HIGH_SCORE_INF) {
            $cls = 'high';
        }

        return [$score, $cls];
    }

    protected static function getDirectory(bool $core = true): string {
        return dirname(__DIR__, 3) . ($core ? '/assets/rules/core' : '/assets/rules/custom');
    }

    protected static function getNamespace(bool $core = true): string {
        return $core ? '\\Tirreno\\Rules\\Core' : '\\Tirreno\\Rules\\Custom';
    }

    protected static function getClassFilename(string $filename, bool $core = true): string {
        return self::getDirectory($core) . '/' . $filename;
    }

    public static function getAllRulesObjects(?\Ruler\RuleBuilder $ruleBuilder): array {
        $core  = self::getRulesClasses(true);
        $local = self::getRulesClasses(false);
        //$core  = self::getRulesClasses(true);

        $total = $local['imported'] + $core['imported'];

        foreach ($total as $uid => $cls) {
            $total[$uid] = new $cls($ruleBuilder, []);
        }

        return $total;
    }

    public static function getSingleRuleObject(string $uid, ?\Ruler\RuleBuilder $ruleBuilder): ?\Tirreno\Assets\Rule {
        $obj = null;
        $cores = [false, true];

        foreach ($cores as $core) {
            $namespace  = self::getNamespace($core);

            $filename   = self::getClassFilename($uid . '.php', $core);
            $cls        = $namespace . '\\' . $uid;

            try {
                self::validateRuleClass($uid, $filename, $cls, $core);
                $obj = new $cls($ruleBuilder, []);
                break;
            } catch (\Throwable $e) {
                self::log('Rule validation failed at file ' . $filename . ' with message ' . $e->getMessage());
            }
        }

        return $obj;
    }

    public static function getRulesClasses(bool $core): array {
        $dir        = self::getDirectory($core);
        $namespace  = self::getNamespace($core);

        $out = [];
        $failed = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        $namePattern = $core ? '/^[A-WY-Z][0-9]{2,3}$/' : '/^[A-Z][0-9]{2,3}$/';

        foreach ($iter as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $name = null;
                try {
                    $name = basename($file->getFilename(), '.php');

                    if (!preg_match($namePattern, $name)) {
                        continue;
                    }

                    $filePath = $file->getRealPath();

                    $cls = $namespace . '\\' . $name;

                    self::validateRuleClass($name, $filePath, $cls, $core);

                    $out[$name] = $cls;
                } catch (\Throwable $e) {
                    $failed[] = $name;
                    self::log('Fail on include_once: ' . $e->getMessage());
                }
            }
        }

        return ['imported' => $out, 'failed' => $failed];
    }

    private static function validateRuleClass(string $uid, string $filename, string $classname, bool $core): string {
        $reflection = self::validateClass($filename, $classname);

        if (!$core && !str_starts_with($uid, 'X')) {
            $parentClassName = $reflection->getParentClass()->getName();
            if ('\\' . $parentClassName !== self::getNamespace($core) . '\\' . $uid) {
                throw new \LogicException("Class {$classname} in assets has invalid parent class {$parentClassName}");
            }
        }

        return $classname;
    }
}
