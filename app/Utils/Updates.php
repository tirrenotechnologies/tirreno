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

namespace Tirreno\Utils;

class Updates {
    private const UPDATES_LIST = [
        \Tirreno\Updates\Update001::class,
        \Tirreno\Updates\Update002::class,
        \Tirreno\Updates\Update003::class,
        \Tirreno\Updates\Update004::class,
        \Tirreno\Updates\Update005::class,
        \Tirreno\Updates\Update006::class,
        \Tirreno\Updates\Update007::class,
        \Tirreno\Updates\Update008::class,
    ];

    public static function syncUpdates(): void {
        $f3 = \Base::instance();
        $updates = new \Tirreno\Models\Updates($f3);
        $applied = $updates->checkDb('core', self::UPDATES_LIST);

        if ($applied) {
            $controller = new \Tirreno\Controllers\Admin\Rules\Data();
            // update only core rules
            $controller->updateRules(false);
        }

        \Tirreno\Utils\Routes::callExtra('UPDATES');
    }
}
