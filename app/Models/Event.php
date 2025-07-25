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

namespace Models;

class Event extends \Models\BaseSql {
    use \Traits\Enrichment\Ips;
    use \Traits\Enrichment\Emails;
    use \Traits\Enrichment\TimeZones;

    protected $DB_TABLE_NAME = 'event';

    public function getLastEvent(int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event.id,
                event.time
            FROM
                event
            WHERE
                event.key = :api_key

            ORDER BY id DESC
            LIMIT 1 OFFSET 0'
        );

        return $this->execQuery($query, $params);
    }

    public function getEventDetails(int $id, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':id' => $id,
        ];

        $query = (
            'SELECT
                event.id,
                event.time AS event_time,
                event.http_code AS event_http_code,

                event_ip.ip,
                event_ip.cidr,
                event_ip.data_center,
                event_ip.vpn,
                event_ip.tor,
                event_ip.relay,
                event_ip.starlink,
                event_ip.id                     AS ipId,
                event_ip.blocklist              AS blocklist,
                event_ip.alert_list             AS ip_alert_list,
                event_ip.shared                 AS ip_users,
                event_ip.total_visit            AS ip_events,
                event_ip.checked,
                event_ip.fraud_detected         AS fraud_detected,

                event_isp.asn,
                event_isp.description,
                event_isp.id   AS ispId,
                event_isp.name AS netname,

                event_url.url,
                event_url.title,
                event_url_query.query,
                event_url.id AS url_id,

                event_referer.referer,

                event_account.score,
                event_account.score_updated_at,
                event_account.fraud,
                event_account.reviewed,
                event_account.added_to_review,
                event_account.firstname,
                event_account.lastname,
                event_account.is_important,
                event_account.latest_decision,
                event_account.id     AS accountid,
                event_account.userid AS accounttitle,

                event_phone.phone_number           AS phonenumber,
                event_phone.type                   AS phone_type,
                event_phone.carrier_name,
                event_phone.profiles               AS phone_profiles,
                event_phone.invalid                AS phone_invalid,
                event_phone.shared                 AS phone_users,
                event_phone.alert_list             AS phone_alert_list,
                event_phone.fraud_detected         AS phone_fraud_detected,
                phone_countries.id                  AS phone_country_id,
                phone_countries.iso                 AS phone_country_iso,
                phone_countries.value               AS phone_full_country,

                event_email.email,
                event_email.profiles,
                event_email.data_breach,
                event_email.data_breaches,
                event_email.earliest_breach        AS email_earliest_breach,
                event_email.blockemails            AS blockemails,
                event_email.alert_list             AS email_alert_list,
                event_email.fraud_detected         AS email_fraud_detected,

                current_email.email                 AS current_email,

                event_domain.id                    AS domainId,
                event_domain.domain,
                event_domain.tranco_rank,
                event_domain.blockdomains,
                event_domain.disposable_domains,
                event_domain.free_email_provider,
                event_domain.creation_date         AS domain_creation_date,
                event_domain.disabled              AS domain_disabled,
                event_domain.expiration_date       AS domain_expiration_date,
                event_domain.return_code           AS domain_return_code,

                ip_countries.id                 AS country_id,
                ip_countries.id                 AS ip_country_id,
                ip_countries.iso                AS ip_country_iso,
                ip_countries.value              AS ip_full_country,

                event_device.lang,
                event_device.user_agent AS deviceId,
                event_device.created AS device_created,

                event_ua_parsed.ua,
                event_ua_parsed.os_name,
                event_ua_parsed.os_version,
                event_ua_parsed.browser_name,
                event_ua_parsed.browser_version,
                event_ua_parsed.device AS device_name,
                event_ua_parsed.modified AS ua_modified,

                event_type.name  AS event_type_name,
                event_type.value AS event_type,

                event_http_method.name AS event_http_method_name

            FROM
                event

            INNER JOIN event_account
            ON (event.account = event_account.id)

            INNER JOIN event_url
            ON (event.url = event_url.id)

            LEFT JOIN event_referer
            ON (event.referer = event_referer.id)

            FULL OUTER JOIN event_url_query
            ON (event.query = event_url_query.id)

            INNER JOIN event_device
            ON (event.device = event_device.id)

            INNER JOIN event_type
            ON (event.type = event_type.id)

            INNER JOIN event_ua_parsed
            ON (event_device.user_agent = event_ua_parsed.id)

            INNER JOIN event_ip
            ON (event.ip = event_ip.id)

            LEFT JOIN event_isp
            ON (event_ip.isp = event_isp.id)

            INNER JOIN countries AS ip_countries
            ON (event_ip.country = ip_countries.id)

            LEFT JOIN event_email
            ON (event.email = event_email.id)

            LEFT JOIN event_email AS current_email
            ON (event_account.lastemail = current_email.id)

            LEFT JOIN event_domain
            ON (event_email.domain = event_domain.id)

            LEFT JOIN event_phone
            ON (event_account.lastphone = event_phone.id)

            LEFT JOIN countries AS phone_countries
            ON (phone_countries.id = event_phone.country_code)

            LEFT JOIN event_http_method
            ON (event.http_method = event_http_method.id)

            WHERE
                event.key = :api_key
                AND event.id = :id
            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        $this->calculateIpType($results);
        $this->calculateEmailReputation($results);
        //$this->translateTimeZones($results, ['event_time', 'domain_creation_date']);

        if (count($results)) {
            $results = $results[0];

            $spamlist = $results['ip_type'] === 'Spam list';
            $results['spamlist'] = $spamlist;

            $model = new \Models\User();
            $results['score_details'] = $model->getApplicableRulesByAccountId($results['accountid'], $apiKey);
            $results['score_calculated'] = $results['score'] !== null;
        }

        return $results;
    }
}
