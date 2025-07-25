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

class SuspiciousWordsSql extends SuspiciousWords {
    protected static string $extensionFile = 'sql.txt';

    protected static array $words = [
        '--',
        '/*',
        '*/',
        'pg_',
        '\');',     // should be %'%)%;% ?
        'alter ',
        'select',
        'waitfor',
        'delay',
        'delete',
        'drop',
        'dbcc',
        'schema',
        'exists',
        'cmdshell',
        '%2A',      // *
        '%27',      // '
        '%22',      // "
        '%2D',      // -
        '%2F',      // /
        '%5C',      // \
        '%3B',      // ;
        '%23',      // #
        '%2B',      // +
        '%3D',      // =
        '%28',      // (
        '%29',      // )
    ];
}
