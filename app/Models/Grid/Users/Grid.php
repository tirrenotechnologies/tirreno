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

namespace Models\Grid\Users;

class Grid extends \Models\Grid\Base\Grid {
    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->query = new Query($apiKey);
    }

    public function getUsersByIpId(int $ipId): array {
        $params = [':ip_id' => $ipId];

        return $this->getGrid($this->idsModel->getUsersIdsByIpId(), $params);
    }

    public function getUsersByIspId(int $ispId): array {
        $params = [':isp_id' => $ispId];

        return $this->getGrid($this->idsModel->getUsersIdsByIspId(), $params);
    }

    public function getUsersByDomainId(int $domainId): array {
        $params = [':domain_id' => $domainId];

        return $this->getGrid($this->idsModel->getUsersIdsByDomainId(), $params);
    }

    public function getUsersByCountryId(int $countryId): array {
        $params = [':country_id' => $countryId];

        return $this->getGrid($this->idsModel->getUsersIdsByCountryId(), $params);
    }

    public function getUsersByDeviceId(int $deviceId): array {
        $params = [':device_id' => $deviceId];

        return $this->getGrid($this->idsModel->getUsersIdsByDeviceId(), $params);
    }

    public function getUsersByResourceId(int $resourceId): array {
        $params = [':resource_id' => $resourceId];

        return $this->getGrid($this->idsModel->getUsersIdsByResourceId(), $params);
    }

    public function getAllUsers(): array {
        return $this->getGrid();
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['time', 'lastseen', 'latest_decision', 'created', 'score_updated_at'];

        $this->translateTimeZones($result, $fields);
    }
}
