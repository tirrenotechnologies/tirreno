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

class Domain extends \Tirreno\Controllers\Services\Base {
    public function checkIfOperatorHasAccess(int $domainId, int $apiKey): bool {
        return tirreno('models')->domain->checkAccess($domainId, $apiKey);
    }

    public function getDomainDetails(int $domainId, int $apiKey): array {
        $result = tirreno('models')->domain->getFullDomainInfoById($domainId, $apiKey);

        $tsColumns = ['lastseen'];
        $result = tirreno('utils')->timezones->localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }

    public function isEnrichable(int $apiKey): bool {
        return tirreno('models')->apiKeys->attributeIsEnrichable('domain', $apiKey);
    }
}
