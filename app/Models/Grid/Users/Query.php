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

namespace Models\Grid\Users;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'event_account.id DESC';
    protected $dateRangeField = 'event_account.lastseen';

    protected $allowedColumns = ['score', 'accounttitle', 'firstname', 'lastname', 'created', 'total_visit', 'fraud', 'id'];

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            "SELECT
                TEXT(date_trunc('day', event_account.created)::date) AS created_day,

                event_account.id,
                event_account.is_important,
                event_account.id AS accountid,
                event_account.userid AS accounttitle,
                event_account.score,
                event_account.score_updated_at,
                event_account.created,
                event_account.fraud,
                event_account.reviewed,
                event_account.firstname,
                event_account.lastname,
                event_account.lastseen as lastseen,
                event_account.total_visit,
                event_account.total_ip,
                event_account.total_device,
                event_account.total_country,
                event_account.latest_decision,

                event_email.email,
                event_email.blockemails

            FROM
                event_account

            LEFT JOIN event_email
            ON (event_account.lastemail = event_email.id)

            WHERE
                event_account.key = :api_key
                %s"
        );

        $this->applySearch($query, $queryParams);
        $this->applyRules($query, $queryParams);
        $this->applyScore($query, $queryParams);
        $this->applyOrder($query);
        $this->applyLimit($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = (
            'SELECT
                COUNT (event_account.id)

            FROM
                event_account

            LEFT JOIN event_email
            ON (event_account.lastemail = event_email.id)

            WHERE
                event_account.key = :api_key
                %s'
        );

        $this->applySearch($query, $queryParams);
        $this->applyRules($query, $queryParams);
        $this->applyScore($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $search = $this->f3->get('REQUEST.search');
        $searchConditions = $this->injectIdQuery('event_account.id', $queryParams);

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                " AND
                (
                    LOWER(REPLACE(
                            COALESCE(event_account.firstname, '') ||
                            COALESCE(event_account.lastname, '') ||
                            COALESCE(event_account.firstname, ''),
                            ' ', '')) LIKE LOWER(REPLACE(:search_value, ' ', '')) OR
                    LOWER(event_email.email)       LIKE LOWER(:search_value) OR
                    LOWER(event_account.userid)     LIKE LOWER(:search_value) OR

                    TO_CHAR(event_account.created::timestamp without time zone, 'dd/mm/yyyy hh24:mi:ss') LIKE :search_value
                )"
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search and ids into request
        $query = sprintf($query, $searchConditions);
    }

    private function applyRules(string &$query, array &$queryParams): void {
        $ruleUids = $this->f3->get('REQUEST.ruleUids');
        if ($ruleUids === null) {
            return;
        }

        $uids = [];
        foreach ($ruleUids as $key => $ruleUid) {
            $uids[] = ['uid' => $ruleUid];
        }

        $query .= ' AND score_details @> (:rules_uids)::jsonb';
        $queryParams[':rules_uids'] = json_encode($uids);
    }

    private function applyScore(string &$query, array &$queryParams): void {
        $scoresRanges = $this->f3->get('REQUEST.scoresRange');
        if ($scoresRanges  === null) {
            return;
        }

        $clauses = [];
        foreach ($scoresRanges as $key => $scoreBase) {
            $clauses[] = sprintf('event_account.score >= :score_base_%s AND event_account.score <= :score_base_%s + 10', $key, $key);
            $queryParams[':score_base_' . $key] = intval($scoreBase);
        }

        $query .= ' AND (' . implode(' OR ', $clauses) . ')';
    }
}
