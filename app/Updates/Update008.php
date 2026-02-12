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

namespace Tirreno\Updates;

class Update008 extends Base {
    public static string $version = 'v0.9.12';

    public static function apply(\DB\SQL $database): void {
        $queries = [
            'ALTER TABLE ONLY dshb_operators_rules ADD CONSTRAINT dshb_operators_rules_key_rule_uid_key UNIQUE (key, rule_uid)',
            'DROP TABLE dshb_operators_change_email',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }
    }
}
