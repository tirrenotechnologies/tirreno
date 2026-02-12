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

namespace Tirreno\Models\Grid\Base;

class Grid extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event';

    protected ?object $idsModel = null;
    protected ?object $queryModel = null;
    protected ?int $apiKey = null;

    protected function getGrid(?string $ids = null, array $idsParams = []): array {
        $this->setIds($ids, $idsParams);

        $draw = $this->f3->get('REQUEST.draw') ?? 1;
        $data = $this->getData();
        $total = $this->getTotal();

        $dateRange = \Tirreno\Utils\DateRange::getDatesRangeFromRequest();

        return [
            'data' => $data,
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'dateRange' => $dateRange,
        ];
    }

    public function setIds(?string $ids, array $idsParams): void {
        $this->queryModel->setIds($ids, $idsParams);
    }

    protected function getData(): array {
        [$query, $params] = $this->queryModel->getData();

        $results = $this->execQuery($query, $params);

        $this->convertTimeToUserTimezone($results);
        $this->calculateCustomParams($results);

        return $results;
    }

    protected function getTotal(): int {
        [$query, $params] = $this->queryModel->getTotal();

        $results = $this->execQuery($query, $params);

        return $results[0]['count'];
    }

    protected function convertTimeToUserTimezone(array &$result): void {
        \Tirreno\Utils\Timezones::translateTimezones($result);
    }

    protected function calculateCustomParams(array &$result): void {
    }
}
