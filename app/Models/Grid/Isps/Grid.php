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

namespace Tirreno\Models\Grid\Isps;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function getIspsByUserId(int $userId, int $apiKey): array {
        $params = [':account_id' => $userId];

        return $this->getGrid($apiKey, $this->idsModel->getIspsIdsByUserId(), $params);
    }

    public function getIspsByDomainId(int $domainId, int $apiKey): array {
        $params = [':domain_id' => $domainId];

        return $this->getGrid($apiKey, $this->idsModel->getIspsIdsByDomainId(), $params);
    }

    public function getIspsByCountryId(int $countryId, int $apiKey): array {
        $params = [':country_id' => $countryId];

        return $this->getGrid($apiKey, $this->idsModel->getIspsIdsByCountryId(), $params);
    }

    public function getIspsByResourceId(int $resourceId, int $apiKey): array {
        $params = [':resource_id' => $resourceId];

        return $this->getGrid($apiKey, $this->idsModel->getIspsIdsByResourceId(), $params);
    }

    public function getIspsByFieldId(int $fieldId, int $apiKey): array {
        $params = [':field_id' => $fieldId];

        return $this->getGrid($apiKey, $this->idsModel->getIspsIdsByFieldId(), $params);
    }

    public function getAll(int $apiKey): array {
        return $this->getGrid($apiKey);
    }
}
