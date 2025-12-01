<?php

/**
 * tirreno ~ open security analytics
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

namespace Models\Grid\Base;

class Ids extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event';

    private $apiKey = null;

    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
    }

    public function execute(string $query, array $params): array {
        $params[':api_key'] = $this->apiKey;

        $data = $this->execQuery($query, $params);
        $results = array_column($data, 'itemid');

        return count($results) ? $results : [-1];
    }
}
