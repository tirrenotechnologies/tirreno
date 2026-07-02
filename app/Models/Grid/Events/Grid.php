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

namespace Tirreno\Models\Grid\Events;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function getEventsByUserId(int $userId, int $apiKey): array {
        $ids = ['userId' => $userId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByIspId(int $ispId, int $apiKey): array {
        $ids = ['ispId' => $ispId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByDomainId(int $domainId, int $apiKey): array {
        $ids = ['domainId' => $domainId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByDeviceId(int $deviceId, int $apiKey): array {
        $ids = ['deviceId' => $deviceId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByResourceId(int $resourceId, int $apiKey): array {
        $ids = ['resourceId' => $resourceId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByCountryId(int $countryId, int $apiKey): array {
        $ids = ['countryId' => $countryId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByIpId(int $ipId, int $apiKey): array {
        $ids = ['ipId' => $ipId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getEventsByFieldId(int $fieldId, int $apiKey): array {
        $ids = ['fieldId' => $fieldId];

        return $this->getGrid($apiKey, null, $ids);
    }

    public function getAll(int $apiKey): array {
        return $this->getGrid($apiKey);
    }

    protected function calculateCustomParams(array &$result): void {
        $result = tirreno('utils')->enrichment->calculateIpType($result);
        $result = tirreno('utils')->enrichment->applyDeviceParams($result);
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['time', 'lastseen', 'session_max_t', 'session_min_t', 'score_updated_at'];

        $result = tirreno('utils')->timezones->translateTimezones($result, $fields);
    }
}
