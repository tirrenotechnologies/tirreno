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

class VersionControl {
    public const versionMajor = 0;
    public const versionMinor = 9;
    public const versionRevision = 3;

    public static function versionString(): string {
        return sprintf('%d.%d.%d', self::versionMajor, self::versionMinor, self::versionRevision);
    }

    public static function fullVersionString(): string {
        return sprintf('v%d.%d.%d', self::versionMajor, self::versionMinor, self::versionRevision);
    }
}
