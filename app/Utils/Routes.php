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

class Routes {
    public static function getCurrentRequestOperator(): \Tirreno\Entities\Operator {
        return tirreno('storage')->get('CURRENT_USER') ?? tirreno('entities')->operator->getById(tirreno('utils')->constants->GUEST_OPERATOR_ID);
    }

    public static function setCurrentRequestOperator(): void {
        tirreno('storage')->set('CURRENT_USER', self::getCurrentSessionOperator());
    }

    public static function getCurrentSessionOperator(): \Tirreno\Entities\Operator {
        $loggedInOperatorId = tirreno('utils')->conversion->intValCheckEmpty(tirreno('session')->get('active_user_id'));
        $loggedInOperatorId = $loggedInOperatorId ? $loggedInOperatorId : tirreno('utils')->constants->GUEST_OPERATOR_ID;

        if (!tirreno('models')->operatorsRoles->tableExists()) {
            tirreno('utils')->updates->syncUpdates();
        }

        return tirreno('entities')->operator->getById($loggedInOperatorId);
    }

    public static function getCurrentRequestApiKey(): ?\Tirreno\Entities\ApiKey {
        return tirreno('storage')->get('CURRENT_KEY');
    }

    public static function setCurrentRequestApiKey(): void {
        tirreno('storage')->set('CURRENT_KEY', self::getCurrentSessionApiKey());
    }

    public static function getCurrentSessionApiKey(): ?\Tirreno\Entities\ApiKey {
        $keyId = tirreno('storage')->get('TEST_API_KEY_ID');

        if (!$keyId) {
            $keyId = tirreno('utils')->conversion->intValCheckEmpty(tirreno('session')->get('active_key_id'));
        }

        return $keyId ? tirreno('entities')->apiKey->getById($keyId) : null;
    }

    public static function callExtra(string $method, mixed ...$extra): string|array|null {
        $method = tirreno('storage')->get('EXTRA_' . $method);

        return $method && is_callable($method) ? $method(...$extra) : null;
    }
}
