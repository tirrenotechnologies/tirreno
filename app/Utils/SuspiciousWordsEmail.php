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

class SuspiciousWordsEmail extends SuspiciousWords {
    protected static string $extensionFile = 'email.txt';

    protected static array $words = [
        'spam',
        'test',
        'gummie',
        'dummy',
        '123',
        '321',
        '000',
        '111',
        '222',
        '333',
        '444',
        '555',
        '666',
        '777',
        '888',
        '999',
    ];
}
