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

namespace Models\Grid\Base;

class Query {
    use \Traits\Debug;
    use \Traits\DateRange;

    protected $f3 = null;
    protected $apiKey = null;
    protected $ids = null;
    protected $idsParams = [];
    protected $itemKey = null;
    protected $itemId = null;

    protected $defaultOrder = null;
    protected $dateRangeField = 'event_country.lastseen';

    protected $allowedColumns = [];

    public function __construct(int $apiKey) {
        $this->f3 = \Base::instance();
        $this->apiKey = $apiKey;
    }

    public function setIds(?string $ids, array $idsParams): void {
        $this->ids = $ids;
        $this->idsParams = $idsParams;
        if (count($this->idsParams)) {
            $this->itemKey = array_keys($this->idsParams)[0];
            $this->itemId = $this->idsParams[$this->itemKey];
        }
    }

    protected function applyOrder(string &$query): void {
        $request = $this->f3->get('REQUEST');

        $order = $request['order'] ?? [];
        $columns = $request['columns'] ?? [];

        $orderCondition = $this->defaultOrder;

        if (count($order) && count($columns)) {
            $orderClauses = [];
            foreach ($order as $orderData) {
                $sortDirection = $orderData['dir'] === 'asc' ? 'ASC' : 'DESC';
                $columnIndex = $orderData['column'];
                $sortColumn = $columns[$columnIndex]['data'];
                if (in_array($sortColumn, $this->allowedColumns)) {
                    $orderClauses[] = sprintf('%s %s', $sortColumn, $sortDirection);
                }
            }

            if (count($orderClauses)) {
                $orderCondition = implode(', ', $orderClauses);
            }
        }

        if ($orderCondition) {
            $query .= sprintf(' ORDER BY %s', $orderCondition);
        }
    }

    protected function applyDateRange(string &$query, array &$queryParams): void {
        $params = $this->f3->get('GET');
        $dateRange = $this->getDatesRange($params);

        if ($dateRange) {
            $searchConditions = (
                "AND {$this->dateRangeField} >= :start_time
                AND {$this->dateRangeField} <= :end_time
                %s"
            );

            $query = sprintf($query, $searchConditions);
            $queryParams[':end_time'] = $dateRange['endDate'];
            $queryParams[':start_time'] = $dateRange['startDate'];
        }
    }

    protected function applyLimit(string &$query, array &$queryParams): void {
        $request = $this->f3->get('REQUEST');

        $start = $request['start'] ?? null;
        $length = $request['length'] ?? null;

        if (isset($start) && isset($length)) {
            $query .= ' LIMIT :length OFFSET :start';

            $queryParams[':start'] = $start;
            $queryParams[':length'] = $length;
        }
    }

    protected function getQueryParams(): array {
        return [':api_key' => $this->apiKey];
    }

    public function injectIdQuery(string $field, &$params): string {
        $idsQuery = $this->ids;
        if ($idsQuery === null || $idsQuery === '') {
            return '';
        }
        $idsParams = $this->idsParams;

        foreach ($idsParams as $key => $value) {
            if (!array_key_exists($key, $params) || $params[$key] === null) {
                $params[$key] = $value;
            }
        }

        return " AND $field IN ($idsQuery)";
    }
}
