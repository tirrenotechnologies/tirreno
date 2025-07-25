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

namespace Models\Context;

class User extends Base {
    use \Traits\Enrichment\Emails;

    public function getContext(array $accountIds, int $apiKey): array {
        $results = $this->getDetails($accountIds, $apiKey);

        $this->calculateEmailReputationForContext($results);

        $recordsByAccount = [];
        foreach ($results as $item) {
            $recordsByAccount[$item['ea_id']] = $item;
        }

        return $recordsByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $query = (
            "SELECT
                event_account.id                        AS ea_id,
                event_account.created                   AS ea_created,
                event_account.lastseen                  AS ea_lastseen,
                event_account.total_visit               AS ea_total_visit,
                event_account.total_country             AS ea_total_country,
                event_account.total_ip                  AS ea_total_ip,
                event_account.total_device              AS ea_total_device,
                event_account.firstname                 AS ea_firstname,
                event_account.lastname                  AS ea_lastname,

                event_email.email                       AS ee_email,
                event_email.blockemails                 AS ee_blockemails,
                event_email.data_breach                 AS ee_data_breach,
                -- event_email.profiles                    AS ee_profiles,
                event_email.checked                     AS ee_checked,

                event_domain.discovery_date             AS ed_discovery_date,
                event_domain.blockdomains               AS ed_blockdomains,
                event_domain.disposable_domains         AS ed_disposable_domains,
                -- event_domain.total_account              AS ed_total_account,
                event_domain.free_email_provider        AS ed_free_provider,
                event_domain.tranco_rank                AS ed_tranco_rank,
                event_domain.creation_date              AS ed_creation_date,
                event_domain.expiration_date            AS ed_expiration_date,
                event_domain.return_code                AS ed_return_code,
                event_domain.closest_snapshot           AS ed_closest_snapshot,
                event_domain.mx_record                  AS ed_mx_record,

                lastemail_record.email                  AS le_email,
                lastemail_record.blockemails            AS le_blockemails,
                lastemail_record.data_breach            AS le_data_breach,
                -- lastemail_record.profiles               AS le_profiles,
                lastemail_record.checked                AS le_checked,
                lastemail_record.fraud_detected         AS le_fraud_detected,
                lastemail_record.alert_list             AS le_alert_list,

                lastdomain_record.disposable_domains    AS ld_disposable_domains,
                lastdomain_record.free_email_provider   AS ld_free_email_provider,
                lastdomain_record.blockdomains          AS ld_blockdomains,
                lastdomain_record.mx_record             AS ld_mx_record,
                lastdomain_record.disabled              AS ld_disabled,
                lastdomain_record.creation_date         AS ld_creation_date,
                lastdomain_record.tranco_rank           AS ld_tranco_rank,

                lastphone_record.phone_number           AS lp_phone_number,
                lastphone_record.country_code           AS lp_country_code,
                lastphone_record.invalid                AS lp_invalid,
                lastphone_record.fraud_detected         AS lp_fraud_detected,
                lastphone_record.alert_list             AS lp_alert_list

            FROM
                event_account

            LEFT JOIN event_phone
            ON (event_account.id = event_phone.account_id)

            LEFT JOIN event_email
            ON event_account.id = event_email.account_id

            LEFT JOIN event_domain
            ON event_email.domain = event_domain.id

            LEFT JOIN event_email AS lastemail_record
            ON event_account.lastemail = lastemail_record.id

            LEFT JOIN event_phone AS lastphone_record
            ON event_account.lastphone = lastphone_record.id

            LEFT JOIN event_domain AS lastdomain_record
            ON lastemail_record.domain = lastdomain_record.id

            WHERE
                event_account.key = :api_key
                AND event_account.id IN ({$placeHolders})"
        );

        return $this->execQuery($query, $params);
    }
}
