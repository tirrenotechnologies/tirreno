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

    public static function loadAllRules(): void {
        self::getRulesClasses(false);
        self::getRulesClasses(true);
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

                    require_once $filePath;

                    $cls = $namespace . '\\' . $name;

                    if (!class_exists($cls, false)) {
                        throw new \LogicException("Class {$cls} not found after including {$filePath}");
                    }

                    $reflection = new \ReflectionClass($cls);
                    $reflectionFileName = $reflection->getFileName();

                    if (realpath($reflectionFileName) !== realpath($filePath)) {
                        throw new \LogicException("Class {$cls} is defined in {$reflectionFileName}, not in {$filePath}");
                    }

                    if (!$core && !str_starts_with($name, 'X')) {
                        $parentClassName = $reflection->getParentClass()?->getName();
                        if ('\\' . $parentClassName !== self::$coreRulesNamespace . '\\' . $name) {
                            throw new \LogicException("Class {$cls} in assets has invalid parent class {$parentClassName}");
                        }
                    }

                    $out[$name] = $cls;
                } catch (\Throwable $e) {
                    $failed[] = $name;
                    error_log('Fail on require_once: ' . $e->getMessage());
                }
            }
        }

        return ['imported' => $out, 'failed' => $failed];
    }
}
