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

namespace Updates;

class Update001 extends Base {
    public static $version = 'v0.9.4';

    public static function up($db) {
        $queries = [
            'ALTER TABLE dshb_api ADD COLUMN blacklist_threshold INTEGER DEFAULT -1',
            'ALTER TABLE dshb_api ADD COLUMN review_queue_threshold INTEGER DEFAULT 33',
        ];
        foreach ($queries as $sql) {
            $db->exec($sql);
        }
    }
}
