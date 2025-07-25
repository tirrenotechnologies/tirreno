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

abstract class SuspiciousWords {
    protected static string $extensionFile = '';
    protected static array $words = [];

    private static function getExtension(): ?array {
        $filename =  dirname(__DIR__, 2) . '/assets/suspiciousWords/' . static::$extensionFile;

        return file_exists($filename) ? file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : null;
    }

    public static function getWords(): array {
        return self::getExtension() ?? static::$words;
    }
}
