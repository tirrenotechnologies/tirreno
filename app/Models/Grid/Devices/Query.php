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

namespace Models\Grid\Devices;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_device.created DESC';
    protected $dateRangeField = 'event_device.lastseen';

    protected $allowedColumns = ['created', 'device', 'os_name', 'browser_name', 'lang', 'modified', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_device.id,
                event_device.lang,
                event_device.created,
                event_ua_parsed.device,
                event_ua_parsed.browser_name,
                event_ua_parsed.browser_version,
                event_ua_parsed.os_name,
                event_ua_parsed.os_version,
                event_ua_parsed.ua,
                event_ua_parsed.modified
            FROM
                event_device
            LEFT JOIN event_ua_parsed
            ON (event_device.user_agent = event_ua_parsed.id)

            WHERE
                event_device.key = :api_key
            %s'
        );

        $this->applySearch($query, $queryParams);
        $this->applyOrder($query);
        $this->applyLimit($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                COUNT(DISTINCT event_device.id)

            FROM
                event_device

            WHERE
                event_device.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        //$search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_device.id', $queryParams);

        //Add ids into request
        $query = sprintf($query, $searchConditions);
    }
}
