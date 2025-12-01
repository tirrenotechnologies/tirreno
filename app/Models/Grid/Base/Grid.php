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

class Grid extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event';

    protected $idsModel = null;
    protected $apiKey = null;
    protected $query = null;

    protected function getGrid(?string $ids = null, array $idsParams = []): array {
        $this->setIds($ids, $idsParams);

        $draw = $this->f3->get('REQUEST.draw') ?? 1;
        $data = $this->getData();
        $total = $this->getTotal();

        $dateRange = \Utils\DateRange::getDatesRangeFromRequest();

        return [
            'data' => $data,
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'dateRange' => $dateRange,
        ];
    }

    public function setIds(?string $ids, array $idsParams): void {
        $this->query->setIds($ids, $idsParams);
    }

    protected function getData(): array {
        [$query, $params] = $this->query->getData();

        $results = $this->execQuery($query, $params);

        $this->convertTimeToUserTimezone($results);
        $this->calculateCustomParams($results);

        return $results;
    }

    protected function getTotal(): int {
        [$query, $params] = $this->query->getTotal();

        $results = $this->execQuery($query, $params);

        return $results[0]['count'];
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        \Utils\TimeZones::translateTimeZones($result);
    }

    protected function calculateCustomParams(array &$result): void {
    }
}
