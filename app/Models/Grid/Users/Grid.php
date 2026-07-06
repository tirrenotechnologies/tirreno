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

namespace Tirreno\Models\Grid\Users;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function getUsersByIpId(int $ipId, int $apiKey): array {
        $params = [':ip_id' => $ipId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByIpId(), $params);
    }

    public function getUsersByIspId(int $ispId, int $apiKey): array {
        $params = [':isp_id' => $ispId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByIspId(), $params);
    }

    public function getUsersByDomainId(int $domainId, int $apiKey): array {
        $params = [':domain_id' => $domainId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByDomainId(), $params);
    }

    public function getUsersByCountryId(int $countryId, int $apiKey): array {
        $params = [':country_id' => $countryId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByCountryId(), $params);
    }

    public function getUsersByDeviceId(int $deviceId, int $apiKey): array {
        $params = [':device_id' => $deviceId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByDeviceId(), $params);
    }

    public function getUsersByResourceId(int $resourceId, int $apiKey): array {
        $params = [':resource_id' => $resourceId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByResourceId(), $params);
    }

    public function getUsersByFieldId(int $fieldId, int $apiKey): array {
        $params = [':field_id' => $fieldId];

        return $this->getGrid($apiKey, $this->idsModel->getUsersIdsByFieldId(), $params);
    }

    public function getAll(int $apiKey): array {
        return $this->getGrid($apiKey);
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['time', 'lastseen', 'latest_decision', 'created', 'score_updated_at'];

        $result = tirreno('utils')->timezones->translateTimezones($result, $fields);
    }
}
