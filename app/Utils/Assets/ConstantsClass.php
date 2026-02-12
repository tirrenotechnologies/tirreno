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

class ConstantsClass extends Base {
    protected static function getDirectory(): string {
        return dirname(__DIR__, 3) . '/assets/dashboard';
    }

    protected static function getClassFilename(string $filename): string {
        return self::getDirectory() . '/' . $filename;
    }

    protected static function getNamespace(): string {
        return '\\Tirreno\\Dashboard';
    }

    public static function getConstantsObj(): ?\Tirreno\Assets\Constants {
        $obj = null;

        $filename   = self::getClassFilename('Constants.php');
        $cls        = self::getNamespace() . '\\Constants';

        try {
            self::validateClass($filename, $cls);
            $obj = new $cls();
        } catch (\Throwable $e) {
            self::log('Constants validation failed with error ' . $e->getMessage());
        }

        return $obj;
    }
}
