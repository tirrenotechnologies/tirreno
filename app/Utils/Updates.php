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

namespace Utils;

class Updates {
    private const UPDATES_LIST = [
        \Updates\Update001::class,
        \Updates\Update002::class,
        \Updates\Update003::class,
        \Updates\Update004::class,
        \Updates\Update005::class,
        \Updates\Update006::class,
    ];

    public static function syncUpdates() {
        $f3 = \Base::instance();
        $updates = new \Models\Updates($f3);
        $applied = $updates->checkDb('core', self::UPDATES_LIST);

        if ($applied) {
            $controller = new \Controllers\Admin\Rules\Data();
            // update only core rules
            $controller->updateRules(false);
        }

        \Utils\Routes::callExtra('UPDATES');
    }
}
