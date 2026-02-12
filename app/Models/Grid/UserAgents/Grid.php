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

namespace Tirreno\Models\Grid\UserAgents;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->queryModel = new Query($apiKey);
    }

    public function getAll(): array {
        return $this->getGrid();
    }

    protected function calculateCustomParams(array &$result): void {
        \Tirreno\Utils\Enrichment::applyDeviceParams($result);
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['lastseen'];

        \Tirreno\Utils\Timezones::translateTimezones($result, $fields);
    }
}
