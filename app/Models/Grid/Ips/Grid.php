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

namespace Tirreno\Models\Grid\Ips;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function getIpsByUserId(int $userId, int $apiKey): array {
        $params = [':account_id' => $userId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByUserId(), $params);
    }

    public function getIpsByIspId(int $ispId, int $apiKey): array {
        $params = [':isp_id' => $ispId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByIspId(), $params);
    }

    public function getIpsByDomainId(int $domainId, int $apiKey): array {
        $params = [':domain_id' => $domainId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByDomainId(), $params);
    }

    public function getIpsByCountryId(int $countryId, int $apiKey): array {
        $params = [':country_id' => $countryId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByCountryId(), $params);
    }

    public function getIpsByDeviceId(int $deviceId, int $apiKey): array {
        $params = [':device_id' => $deviceId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByDeviceId(), $params);
    }

    public function getIpsByResourceId(int $resourceId, int $apiKey): array {
        $params = [':resource_id' => $resourceId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByResourceId(), $params);
    }

    public function getIpsByFieldId(int $fieldId, int $apiKey): array {
        $params = [':field_id' => $fieldId];

        return $this->getGrid($apiKey, $this->idsModel->getIpsIdsByFieldId(), $params);
    }

    public function getAll(int $apiKey): array {
        return $this->getGrid($apiKey);
    }

    protected function calculateCustomParams(array &$result): void {
        $result = tirreno('utils')->enrichment->calculateIpType($result);
    }
}
