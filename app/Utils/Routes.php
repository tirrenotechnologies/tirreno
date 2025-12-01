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

class Routes {
    private static function getF3(): \Base {
        return \Base::instance();
    }

    public static function getCurrentRequestOperator(): \Models\Operator|false|null {
        return self::getF3()->get('CURRENT_USER');
    }

    public static function setCurrentRequestOperator(): void {
        self::getF3()->set('CURRENT_USER', self::getCurrentSessionOperator());
    }

    public static function getCurrentSessionOperator(): \Models\Operator|false|null {
        $model = new \Models\Operator();
        $loggedInOperatorId = \Utils\Conversion::intValCheckEmpty(self::getF3()->get('SESSION.active_user_id'));

        return $loggedInOperatorId ? $model->getOperatorById($loggedInOperatorId) : null;
    }

    public static function redirectIfUnlogged(string $targetPage = '/'): void {
        if (!boolval(self::getCurrentRequestOperator())) {
            self::getF3()->reroute($targetPage);
        }
    }

    public static function redirectIfLogged(): void {
        if (boolval(self::getCurrentRequestOperator())) {
            self::getF3()->reroute('/');
        }
    }

    public static function callExtra(string $method, mixed ...$extra): string|array|null {
        $method = \Base::instance()->get('EXTRA_' . $method);

        return $method && is_callable($method) ? $method(...$extra) : null;
    }
}
