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

namespace Models\Grid\Blacklist;

class Query extends \Models\Grid\Base\Query {
    protected $defaultOrder = 'created DESC, type ASC, value ASC';
    protected $dateRangeField = 'blacklist.created';

    public function getData(): array {
        $queryParams = $this->getQueryParams();

        $query = ("
            SELECT DISTINCT
                blacklist.is_important,
                blacklist.accountid,
                blacklist.accounttitle,
                blacklist.created,
                blacklist.score_updated_at,
                blacklist.score,
                blacklist.account_email AS email,
                extra.type,
                TRUE AS fraud,
                CASE extra.type
                    WHEN 'ip'    THEN blacklist.ip
                    WHEN 'email' THEN blacklist.email
                    WHEN 'phone' THEN blacklist.phone
                END AS value,
                CASE extra.type
                    WHEN 'ip'    THEN blacklist.ip_id
                    WHEN 'email' THEN blacklist.email_id
                    WHEN 'phone' THEN blacklist.phone_id
                END AS entity_id

            FROM
                (
                SELECT
                    event_account.is_important,
                    event_account.id                AS accountid,
                    event_account.userid            AS accounttitle,
                    event_account.latest_decision   AS created,
                    event_account.score_updated_at,
                    event_account.score,

                    account_email.email              AS account_email,

                    CASE WHEN event_ip.fraud_detected THEN split_part(event_ip.ip::text, '/', 1) ELSE NULL END AS ip,
                    CASE WHEN event_ip.fraud_detected THEN event_ip.id ELSE NULL END AS ip_id,
                    event_ip.fraud_detected AS ip_fraud,

                    CASE WHEN event_email.fraud_detected THEN event_email.email ELSE NULL END AS email,
                    CASE WHEN event_email.fraud_detected THEN event_email.id ELSE NULL END AS email_id,
                    event_email.fraud_detected AS email_fraud,

                    CASE WHEN event_phone.fraud_detected THEN event_phone.phone_number ELSE NULL END AS phone,
                    CASE WHEN event_phone.fraud_detected THEN event_phone.id ELSE NULL END AS phone_id,
                    event_phone.fraud_detected AS phone_fraud

                FROM event

                LEFT JOIN event_account
                ON event_account.id = event.account

                LEFT JOIN event_email AS account_email
                ON event_account.lastemail = account_email.id

                LEFT JOIN event_ip
                ON event_ip.id = event.ip

                LEFT JOIN event_email
                ON event_email.id = event.email

                LEFT JOIN event_phone
                ON event_phone.id = event.phone

                WHERE
                    event_account.key = :api_key AND
                    event_account.fraud IS TRUE AND
                    (
                        event_email.fraud_detected IS TRUE OR
                        event_ip.fraud_detected IS TRUE OR
                        event_phone.fraud_detected IS TRUE
                    )
                ) AS blacklist,
                LATERAL (
                    VALUES
                        (CASE WHEN ip_fraud = true THEN 'ip' END),
                        (CASE WHEN email_fraud = true THEN 'email' END),
                        (CASE WHEN phone_fraud = true THEN 'phone' END)
                ) AS extra(type)

            WHERE
                extra.type IS NOT NULL
                %s
        ");

        $this->applySearch($query, $queryParams);
        $this->applyEntityTypes($query, $queryParams);
        $this->applyOrder($query);
        $this->applyLimit($query, $queryParams);

        return [$query, $queryParams];
    }

    public function getTotal(): array {
        $queryParams = $this->getQueryParams();

        $query = ("
            SELECT COUNT(*)
            FROM (
                SELECT DISTINCT
                    blacklist.accountid,
                    blacklist.created,
                    extra.type,
                    CASE extra.type
                        WHEN 'ip'    THEN blacklist.ip
                        WHEN 'email' THEN blacklist.email
                        WHEN 'phone' THEN blacklist.phone
                    END AS value

                FROM
                    (
                    SELECT
                        event_account.id                AS accountid,
                        event_account.latest_decision   AS created,
                        CASE WHEN event_ip.fraud_detected THEN split_part(event_ip.ip::text, '/', 1) ELSE NULL END AS ip,
                        event_ip.fraud_detected AS ip_fraud,
                        CASE WHEN event_email.fraud_detected THEN event_email.email ELSE NULL END AS email,
                        event_email.fraud_detected AS email_fraud,
                        CASE WHEN event_phone.fraud_detected THEN event_phone.phone_number ELSE NULL END AS phone,
                        event_phone.fraud_detected AS phone_fraud
                    FROM event

                    LEFT JOIN event_account
                    ON event_account.id = event.account

                    LEFT JOIN event_ip
                    ON event_ip.id = event.ip

                    LEFT JOIN event_email
                    ON event_email.id = event.email

                    LEFT JOIN event_phone
                    ON event_phone.id = event.phone

                    WHERE
                        event_account.key = :api_key AND
                        event_account.fraud IS TRUE AND
                        (
                            event_email.fraud_detected IS TRUE OR
                            event_ip.fraud_detected IS TRUE OR
                            event_phone.fraud_detected IS TRUE
                        )
                    ) AS blacklist,
                    LATERAL (
                        VALUES
                            (CASE WHEN ip_fraud = true THEN 'ip' END),
                            (CASE WHEN email_fraud = true THEN 'email' END),
                            (CASE WHEN phone_fraud = true THEN 'phone' END)
                    ) AS extra(type)

                WHERE
                    extra.type IS NOT NULL
                    %s
            ) AS tbl
        ");

        $this->applySearch($query, $queryParams);
        $this->applyEntityTypes($query, $queryParams);

        return [$query, $queryParams];
    }

    private function applySearch(string &$query, array &$queryParams): void {
        $this->applyDateRange($query, $queryParams);

        $searchConditions = '';
        $search = $this->f3->get('REQUEST.search');

        if (is_array($search) && isset($search['value']) && is_string($search['value']) && $search['value'] !== '') {
            $searchConditions .= (
                " AND (
                    LOWER(extra.type)                       LIKE LOWER(:search_value) OR
                    LOWER(CASE extra.type
                        WHEN 'ip'    THEN blacklist.ip
                        WHEN 'email' THEN blacklist.email
                        WHEN 'phone' THEN blacklist.phone
                    END)                                    LIKE LOWER(:search_value) OR
                    TO_CHAR(blacklist.created::timestamp without time zone, 'dd/mm/yyyy hh24:mi:ss') LIKE :search_value
                )"
            );

            $queryParams[':search_value'] = '%' . $search['value'] . '%';
        }

        //Add search into request
        $query = sprintf($query, $searchConditions . ' %s');
    }

    private function applyEntityTypes(string &$query, array &$queryParams): void {
        $searchCondition = '';

        $entityTypeIds = $this->f3->get('REQUEST.entityTypeIds');
        if ($entityTypeIds !== null && count($entityTypeIds)) {
            $clauses = [];

            foreach ($entityTypeIds as $key => $entityTypeId) {
                $clauses[] = 'extra.type = :entity_type_' . $key;
                $queryParams[':entity_type_' . $key] = strtolower(\Utils\Constants::get('ENTITY_TYPES')[$entityTypeId]);
            }

            $searchCondition = ' AND (' . implode(' OR ', $clauses) . ')';
        }

        $query = sprintf($query, $searchCondition);
    }
}
