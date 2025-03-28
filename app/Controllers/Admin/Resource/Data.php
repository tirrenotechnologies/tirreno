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

namespace Controllers\Admin\Resource;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function checkIfOperatorHasAccess(int $resourceId): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Resource();

        return $model->checkAccess($resourceId, $apiKey);
    }

    public function getResourceById(int $resourceId): array {
        $model = new \Models\Resource();
        $result = $model->getResourceById($resourceId);
        $result['lastseen'] = \Utils\ElapsedDate::short($result['lastseen']);

        return $result;
    }
}
