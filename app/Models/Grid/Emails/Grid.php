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

namespace Models\Grid\Emails;

class Grid extends \Models\Grid\Base\Grid {
    use \Traits\Enrichment\Emails;

    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->query = new Query($apiKey);
    }

    public function getEmailsByUserId(int $userId): array {
        $params = [':account_id' => $userId];

        return $this->getGrid($this->idsModel->getEmailsIdsByUserId(), $params);
    }

    protected function calculateCustomParams(array &$result): void {
        $this->calculateEmailReputation($result);
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['lastseen'];

        $this->translateTimeZones($result, $fields);
    }
}
