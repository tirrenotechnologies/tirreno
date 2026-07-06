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

namespace Tirreno\Controllers\Pages;

class Devices extends \Tirreno\Controllers\Pages\Base {
    protected string $page = 'devices';

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function getChart(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getChart($this->apiKey) : [];
    }

    public function getDeviceDetails(): array {
        $this->assertCanView();

        return $this->apiKey && $this->id ? $this->controller->getDeviceDetails($this->id, $this->apiKey) : [];
    }
}
