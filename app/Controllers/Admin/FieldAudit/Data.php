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

namespace Controllers\Admin\FieldAudit;

class Data extends \Controllers\Admin\Base\Data {
    public function checkIfOperatorHasAccess(int $fieldId): bool {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $model = new \Models\FieldAudit();

        return $model->checkAccess($fieldId, $apiKey);
    }

    public function getFieldById(int $fieldId): array {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();

        $model = new \Models\FieldAudit();
        $result = $model->getFieldById($fieldId, $apiKey);
        $result['lastseen'] = \Utils\ElapsedDate::short($result['lastseen']);
        $result['created'] = \Utils\ElapsedDate::short($result['created']);

        return $result;
    }
}
