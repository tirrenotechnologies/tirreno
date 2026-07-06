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

class PagesClasses extends Base {
    // directory with subdirectories
    protected static function getDirectory(bool $core = true): string {
        return dirname(__DIR__, 3) . '/assets/pages';
    }

    protected static function getNamespace(bool $core = true): string {
        return '\\Tirreno\\Pages';
    }

    protected static function getClassFilename(string $filename): string {
        return self::getDirectory() . '/' . lcfirst($filename)  . '/' . $filename;
    }

    public static function getAllPagesObjects(): array {
        $classes = self::getPagesClasses()['imported'];

        foreach ($classes as $name => $cls) {
            $classes[$name] = new $cls();
        }

        return $classes;
    }

    public static function getMenuPages(): array {
        $files = glob(self::getDirectory() . '/*.php') ?: [];
        $pages = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');

            if ($name === 'index' || str_ends_with($name, '.example')) {
                continue;
            }

            $title = null;
            $content = file_get_contents($file);
            if ($content !== false && preg_match('/\$page->setTitle\(\s*([\'"])(.+?)\1\s*\)/', $content, $matches)) {
                $title = $matches[2];
            }

            $pages[$name] = [
                'route' => '/' . $name,
                'title' => $title ?? ucfirst(str_replace(['-', '_'], ' ', $name)),
            ];
        }

        ksort($pages);

        return array_values($pages);
    }

    public static function getSinglePageObject(string $name): ?\Tirreno\Core\Page {
        $obj = null;

        $filename   = self::getClassFilename($name . '.php');
        $cls        = self::getNamespace() . '\\' . $name;

        try {
            self::validateClass($filename, $cls);
            $obj = new $cls();
        } catch (\Throwable $e) {
            tirreno('log')->info('page file %s not found: %s.', $filename, $e->getMessage());
        }

        return $obj;
    }

    public static function getPagesClasses(): array {
        $dir        = self::getDirectory();
        $namespace  = self::getNamespace();

        $out = [];
        $failed = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        foreach ($iter as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $name = null;
                try {
                    $subPath = $iter->getSubPath();
                    $name = basename($file->getFilename(), '.php');

                    if ($subPath === lcfirst($name) && preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
                        $filePath = $file->getRealPath();
                        $cls = $namespace . '\\' . $name;
                        self::validateClass($filePath, $cls);
                        $out[$name] = $cls;
                    }
                } catch (\Throwable $e) {
                    $failed[] = $name;
                    tirreno('log')->info('page file %s not found: %s.', $file->getFilename(), $e->getMessage());
                }
            }
        }

        // load file based route loader class
        $out['FileBasedPage'] = '\\Tirreno\\Core\\FileBasedPage';

        return ['imported' => $out, 'failed' => $failed];
    }
}
