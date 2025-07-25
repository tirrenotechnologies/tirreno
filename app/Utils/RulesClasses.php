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

namespace Utils;

class RulesClasses {
    private const rulesWeight = [
        -20 =>  'positive',
        10 =>   'medium',
        20 =>   'high',
        70 =>   'extreme',
        0 =>    'none',
    ];

    private const rulesTypes = [
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

    private static string $coreRulesNamespace = '\\Controllers\\Admin\\Rules\\Set';
    private static string $assetsRulesNamespace = '\\ExtendedRules';

    public static function getRuleClass(?int $value): string {
        return self::rulesWeight[$value ?? 0] ?? 'none';
    }

    public static function getRuleTypeByUid(string $uid): string {
        return self::rulesTypes[$uid[0]] ?? $uid[0];
    }

    public static function getUserScoreClass(?int $score): array {
        $cls = 'empty';
        if ($score === null) {
            return ['&minus;', $cls];
        }

        if ($score >= \Utils\Constants::get('USER_LOW_SCORE_INF') && $score < \Utils\Constants::get('USER_LOW_SCORE_SUP')) {
            $cls = 'low';
        }

        if ($score >= \Utils\Constants::get('USER_MEDIUM_SCORE_INF') && $score < \Utils\Constants::get('USER_MEDIUM_SCORE_SUP')) {
            $cls = 'medium';
        }

        if ($score >= \Utils\Constants::get('USER_HIGH_SCORE_INF')) {
            $cls = 'high';
        }

        return [$score, $cls];
    }

    private static function getCoreRulesDir(): string {
        return dirname(__DIR__, 1) . '/Controllers/Admin/Rules/Set';
    }

    private static function getAssetsRulesDir(): string {
        return dirname(__DIR__, 2) . '/assets/rules';
    }

    public static function getAllRulesObjects(?\Ruler\RuleBuilder $rb): array {
        $local = self::getRulesClasses(false);
        $core  = self::getRulesClasses(true);

        $total = $local['imported'] + $core['imported'];

        foreach ($total as $uid => $cls) {
            $total[$uid] = new $cls($rb, []);
        }

        return $total;
    }

    public static function getSingleRuleObject(string $uid, ?\Ruler\RuleBuilder $rb): ?\Controllers\Admin\Rules\Set\BaseRule {
        $obj = null;
        $cores = [false, true];

        foreach ($cores as $core) {
            $dir        = $core ? self::getCoreRulesDir() : self::getAssetsRulesDir();
            $namespace  = $core ? self::$coreRulesNamespace : self::$assetsRulesNamespace;

            $filename   = $dir . '/' . $uid . '.php';
            $cls        = $namespace . '\\' . $uid;

            try {
                self::validateRuleClass($uid, $filename, $cls, $core);
                $obj = new $cls($rb, []);
                break;
            } catch (\Throwable $e) {
                error_log('Rule validation failed at file ' . $filename);
            }
        }

        return $obj;
    }

    public static function getRulesClasses(bool $core): array {
        $dir        = $core ? self::getCoreRulesDir() : self::getAssetsRulesDir();
        $namespace  = $core ? self::$coreRulesNamespace : self::$assetsRulesNamespace;

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
                    error_log('Fail on require_once: ' . $e->getMessage());
                }
            }
        }

        return ['imported' => $out, 'failed' => $failed];
    }

    private static function validateRuleClass(string $uid, string $filename, string $classname, bool $core): string {
        $reflection = self::validateObject($filename, $classname);

        if (!$core && !str_starts_with($uid, 'X')) {
            $parentClassName = $reflection->getParentClass()?->getName();
            if ('\\' . $parentClassName !== self::$coreRulesNamespace . '\\' . $uid) {
                throw new \LogicException("Class {$classname} in assets has invalid parent class {$parentClassName}");
            }
        }

        return $classname;
    }

    private static function validateObject(string $filename, string $classname): \ReflectionClass {
        if (!file_exists($filename)) {
            throw new \LogicException("File {$filename} doesn't exist.");
        }

        require_once $filename;

        if (!class_exists($classname, false)) {
            throw new \LogicException("Class {$classname} not found after including {$filename}");
        }

        $reflection = new \ReflectionClass($classname);
        $reflectionFileName = $reflection->getFileName();

        if (realpath($reflectionFileName) !== realpath($filename)) {
            throw new \LogicException("Class {$classname} is defined in {$reflectionFileName}, not in {$filename}");
        }

        return $reflection;
    }
}
