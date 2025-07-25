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

namespace Controllers\Admin\Context;

class Data extends \Controllers\Base {
    private \Models\Context\Data $model;

    public function __construct() {
        $this->model = new \Models\Context\Data();
    }

    public function getContextByAccountIds(array $accountIds, int $apiKey): array {
        return $this->getContext($accountIds, $apiKey);
    }

    public function getContextByAccountId(int $accountId, int $apiKey): array {
        $accountIds = [$accountId];
        $context = $this->getContext($accountIds, $apiKey);

        return $context[$accountId] ?? [];
    }

    private function getContext(array $accountIds, int $apiKey): array {
        return $this->model->getContext($accountIds, $apiKey);
    }
}
