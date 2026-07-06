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

class RulesPresets {
    protected static string $corePath = '/assets/rules/core/';
    protected static string $customPath = '/assets/rules/custom/';
    protected static string $filenamePattern = '/^preset-[a-z0-9]+(?:-[a-z0-9]+)*\.php$/';

    // allow child classes
    protected static array $instances = [];
    protected array $data;

    final protected function __construct() {
        $this->data = $this->loadData();
    }

    protected static function getInstance(): static {
        $class = static::class;

        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = new static();
        }

        return self::$instances[$class];
    }

    public static function getPresets(): array {
        return static::getInstance()->data;
    }

    protected static function getFilenames(bool $core = true): array {
        $result = [];
        $dir = dirname(__DIR__, 3) . ($core ? static::$corePath : static::$customPath);
        $files = scandir($dir);

        foreach ($files as $file) {
            if (preg_match(static::$filenamePattern, $file)) {
                $result[] = $file;
            }
        }

        tirreno('log')->debug('extracted filenames from dir %s: %s', $dir, json_encode($result));

        return $result;
    }

    protected static function getKeyByFilename(string $filename): string {
        $parts = explode('.', $filename);
        $filename = $parts[0];
        $parts = explode('-', $filename);
        array_shift($parts);

        return implode('_', $parts);
    }

    protected static function collectRulesFromFile(string $filename, array $uids, bool $core = true): ?array {
        $file = dirname(__DIR__, 3) . ($core ? static::$corePath : static::$customPath) . $filename;

        if (!file_exists($file) || !is_readable($file)) {
            return null;
        }

        $preset = include $file;
        if (!is_array($preset)) {
            return null;
        }

        $description = $preset['description'] ?? null;
        $rules = $preset['rules'] ?? null;

        if (!is_array($rules)) {
            return null;
        }

        $validRules = [];

        foreach ($rules as $uid => $weight) {
            if (!is_string($uid) || !is_string($weight)) {
                continue;
            }

            $weightInt = tirreno('utils')->constants->RULE_WEIGHT_MAP[$weight] ?? null;
            if (!$weightInt || !isset($uids[$uid])) {
                continue;
            }

            $validRules[$uid] = $weightInt;
        }

        return [
            'key'           => static::getKeyByFilename($filename),
            'description'   => $description,
            'main'          => $validRules,
            'additional'    => [],
        ];
    }

    protected function loadData(): array {
        $presets = [];
        $uids = array_flip(array_column(tirreno('models')->rules->getAll(), 'uid'));

        // custom can override core
        $cores = [true, false];
        foreach ($cores as $core) {
            $filenames = static::getFilenames($core);
            foreach ($filenames as $filename) {
                $result = static::collectRulesFromFile($filename, $uids, $core);

                if ($result) {
                    $presets[$result['key']] = $result;
                }
            }
        }

        return $presets;
    }
}
