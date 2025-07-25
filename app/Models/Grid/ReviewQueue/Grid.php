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

namespace Models\Grid\ReviewQueue;

class Grid extends \Models\Grid\Base\Grid {
    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->query = new Query($apiKey);
    }

    public function getAllUnderReviewUsers(): array {
        return $this->getGrid();
    }

    public function getTotalUnderReviewUsers(): int {
        return $this->getTotal();
    }

    public function getTotalUnderReviewUsersOverall(): int {
        [$query, $params] = $this->query->getTotalOverall();
        $results = $this->execQuery($query, $params);

        return $results[0]['count'];
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        $fields = ['lastseen', 'created', 'score_updated_at', 'added_to_review'];

        $this->translateTimeZones($result, $fields);
    }
}
