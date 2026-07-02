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

namespace Tirreno\Core\Services;

class Session {
    public function getCurrentOperator(): \Tirreno\Entities\Operator {
        return tirreno('utils')->routes->getCurrentRequestOperator();
    }

    public function getCurrentKey(): ?\Tirreno\Entities\ApiKey {
        return tirreno('utils')->routes->getCurrentRequestApiKey();
    }

    public function extractCurrentOperator(): void {
        tirreno('utils')->routes->setCurrentRequestOperator();
        tirreno('utils')->routes->setCurrentRequestApiKey();
    }

    public function setActiveOperator(int $operatorId): void {
        $this->set('active_user_id', $operatorId);
    }

    public function get(string $key): mixed {
        return tirreno('storage')->get('SESSION.' . $key);
    }

    // WARN: $value should be serializable
    public function set(string $key, mixed $value): mixed {
        return tirreno('storage')->set('SESSION.' . $key, $value);
    }

    public function remove(string $key): mixed {
        return tirreno('storage')->remove('SESSION.' . $key);
    }

    public function clear(): void {
        tirreno('storage')->remove('SESSION');
    }
}
