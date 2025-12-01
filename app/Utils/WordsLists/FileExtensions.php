<?php

/**
 * tirreno ~ open security analytics
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

namespace Utils\WordsLists;

class FileExtensions extends Base {
    protected static string $extensionFile = 'file-extensions.php';
    protected static array $words = [];

    public static function getWords(): array {
        return self::getExtension() ?? [];
    }

    public static function getKeys(): array {
        return array_keys(self::getWords());
    }

    public static function getValues(string $key): array {
        return self::getWords()[$key] ?? [];
    }
}
