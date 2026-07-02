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

namespace Tirreno\Controllers\Services;

class FieldAudit extends \Tirreno\Controllers\Services\Base {
    public function checkIfOperatorHasAccess(int $fieldId, int $apiKey): bool {
        return tirreno('models')->fieldAudit->checkAccess($fieldId, $apiKey);
    }

    public function getFieldById(int $fieldId, int $apiKey): array {
        $result = tirreno('models')->fieldAudit->getFieldById($fieldId, $apiKey);
        $result['lastseen'] = tirreno('utils')->elapsedDate->short($result['lastseen']);
        $result['created'] = tirreno('utils')->elapsedDate->short($result['created']);

        return $result;
    }
}
