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

namespace Tirreno\Utils\Http;

final class HeaderUtils {
    /**
     * Ensures the header exists (case-insensitive by name).
     *
     * @param array<int, string> $headers
     *
     * @return array<int, string>
     */
    public static function ensureHeader(array $headers, string $name, string $value): array {
        $needle = strtolower($name) . ':';

        foreach ($headers as $headerLine) {
            $line = strtolower(trim($headerLine));

            if (str_starts_with($line, $needle)) {
                return $headers;
            }
        }

        $header = sprintf('%s: %s', $name, $value);
        $headers[] = $header;

        return $headers;
    }
}
