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

namespace Models\Grid\Resources;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_url.id DESC';
    protected $dateRangeField = 'event_url.lastseen';

    protected $allowedColumns = ['title', 'http_code', 'total_account', 'total_edit', 'total_ip', 'total_visit', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                event_url.id,
                event_url.id AS url_id,
                event_url.key,
                event_url.url,
                event_url.title,
                event_url.http_code,

                event_url.total_visit,
                event_url.total_ip,
                event_url.total_account,
                event_url.total_edit

            FROM
                event_url

            WHERE
                event_url.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);
        $this->applyFileExtensions($query, $queryParams);
        $this->applyOrder($query);
        $this->applyLimit($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                COUNT(event_url.id)

            FROM
                event_url

            WHERE
                event_url.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);
        $this->applyFileExtensions($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getEventsCount(array $ids): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids, 'auth');
        $queryParams = $params + $this->getQueryParams();

        $query = (
            'SELECT
                event.url                                                            AS id,
                COUNT(CASE WHEN event_account.authorized IS TRUE  THEN event.id END) AS authorized_events,
                COUNT(CASE WHEN event_account.authorized IS FALSE THEN event.id END) AS unauthorized_events
            FROM
                event

            LEFT JOIN event_url
            ON event.url = event_url.id

            LEFT JOIN event_account
            ON event.account = event_account.id

            WHERE
                event.key = :api_key
                %s
            GROUP BY event.url'
        );

        $this->applyDateRange($query, $queryParams);
        $query = sprintf($query, ' AND event.url IN (' . $flatIds . ')');

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $search = \Utils\Conversion::getArrayRequestParam('search');
        $searchConditions = $this->injectIdQuery('event_url.id', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                ' AND
                (
                    LOWER(event_url.title)      LIKE LOWER(:search_value) OR
                    LOWER(event_url.url)        LIKE LOWER(:search_value)
                )'
            );
            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }

    private function applyFileExtensions(string &$query, array &$queryParams): void {
        $fileTypeIds = \Utils\Conversion::getArrayRequestParam('fileTypeIds');
        if (!$fileTypeIds) {
            return;
        }

        $list = \Utils\WordsLists\FileExtensions::getWords();
        $keys = \Utils\WordsLists\FileExtensions::getKeys();

        $extensions = [];

        foreach ($fileTypeIds as $fileTypeId) {
            if (array_key_exists($fileTypeId, $keys)) {
                $extensions = array_values(array_unique(array_merge($extensions, $list[$keys[$fileTypeId]])));
            }
        }

        if (!$extensions && count($fileTypeIds) !== 1 && $keys[$fileTypeIds[0]] !== 'Other') {
            return;
        }

        if (!$extensions && count($fileTypeIds) === 1 && $keys[$fileTypeIds[0]] === 'Other') {
            foreach ($list as $key => $value) {
                $extensions = array_values(array_unique(array_merge($extensions, $value)));
            }
        }

        if (!$extensions) {
            return;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($extensions, 'file');

        $queryParams = $queryParams + $params;

        if (count($fileTypeIds) === 1 && $keys[$fileTypeIds[0]] === 'Other') {
            $query .= " AND strpos(event_url.url, '.') > 0 AND '.' || reverse(split_part(reverse(event_url.url), '.', 1)) NOT IN ($flatIds)";
        } else {
            $query .= " AND strpos(event_url.url, '.') > 0 AND '.' || reverse(split_part(reverse(event_url.url), '.', 1)) IN ($flatIds)";
        }
    }

    private function getArrayPlaceholders(array $ids, string $postfix = ''): array {
        $params = [];
        $placeHolders = [];

        $postfix = $postfix !== '' ? '_' . $postfix : '';

        foreach ($ids as $i => $id) {
            $key = sprintf(':item_id_%s%s', $i, $postfix);
            $placeHolders[] = $key;
            $params[$key] = $id;
        }

        $placeHolders = implode(', ', $placeHolders);

        return [$params, $placeHolders];
    }
}
