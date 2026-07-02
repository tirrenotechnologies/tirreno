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

namespace Tirreno\Models\Query;

class Events extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event';
        $this->model = 'events';

        $this->fields = [
            'event_account.id'              => 'user_id',
            'event_account.userid'          => 'user_userid',
            'event_account.fullname'        => 'user_fullname',
            'event_account.firstname'       => 'user_firstname',
            'event_account.middlename'      => 'user_middlename',
            'event_account.lastname'        => 'user_lastname',
            'event_account.score'           => 'user_score',
            'event_account.score_details'   => 'user_score_details',
            'event_account.reviewed'        => 'user_reviewed',
            'event_account.fraud'           => 'user_fraud',

            'event_account.score_recalculate'   => 'user_score_recalculate',
            'event_account.is_important'        => 'user_is_important',
            'event_account.lastip'              => 'user_last_ip',

            'event_account.total_visit'         => 'user_total_visit',
            'event_account.total_country'       => 'user_total_country',
            'event_account.total_ip'            => 'user_total_ip',
            'event_account.total_device'        => 'user_total_device',
            'event_account.total_shared_ip'     => 'user_total_shared_ip',
            'event_account.total_shared_phone'  => 'user_total_shared_phone',

            'event_account.lastseen'            => 'user_lastseen',
            'event_account.created'             => 'user_created',
            'event_account.updated'             => 'user_updated',
            'event_account.score_updated_at'    => 'user_score_updated_at',
            'event_account.latest_decision'     => 'user_latest_decision',
            'event_account.added_to_review'     => 'user_added_to_review',

            'event_email.id'                    => 'email_id',
            'event_email.email'                 => 'email_email',
            //'event_email.data_breach'         => 'email_data_breach',
            //'event_email.blockemails'         => 'email_blockemails',
            'event_email.fraud_detected'        => 'email_fraud_detected',
            //'event_email.domain_contact_email'=> 'email_domain_contact_email',
            'event_email.checked'               => 'email_checked',
            //'event_email.profiles'            => 'email_profiles',
            //'event_email.data_breaches'       => 'email_data_breaches',
            //'event_email.earliest_breach'     => 'email_earliest_breach',
            'event_email.lastseen'              => 'email_lastseen',
            'event_email.created'               => 'email_created',

            'event_phone.id'                    => 'phone_id',
            'event_phone.phone_number'          => 'phone_phone_number',
            'event_phone.shared'                => 'phone_shared',
            'event_phone.fraud_detected'        => 'phone_fraud_detected',
            'event_phone.invalid'               => 'phone_invalid',
            'event_phone.checked'               => 'phone_checked',
            'event_phone.lastseen'              => 'phone_lastseen',
            'event_phone.created'               => 'phone_created',
            'event_phone.updated'               => 'phone_updated',

            // ...
            //'event_domain.'
            //'event_phone.'
            //'countries'

            'event_device.id'           => 'device_id',
            'event_device.account_id'   => 'device_account_id',
            'event_device.lang'         => 'device_lang',
            'event_device.total_visit'  => 'device_total_visit',
            'event_device.lastseen'     => 'device_lastseen',
            'event_device.created'      => 'device_created',
            'event_device.updated'      => 'device_updated',

            'event_ua_parsed.id'                => 'user_agent_id',
            'event_ua_parsed.device'            => 'user_agent_device',
            'event_ua_parsed.browser_name'      => 'user_agent_browser_name',
            'event_ua_parsed.browser_version'   => 'user_agent_browser_version',
            'event_ua_parsed.os_name'           => 'user_agent_os_name',
            'event_ua_parsed.os_version'        => 'user_agent_os_version',
            'event_ua_parsed.ua'                => 'user_agent_user_agent',
            'event_ua_parsed.modified'          => 'user_agent_modified',
            'event_ua_parsed.checked'           => 'user_agent_checked',
            'event_ua_parsed.created'           => 'user_agent_created',

            'event_ip.id'               => 'ip_id',
            'event_ip.ip'               => 'ip_ip',
            'event_ip.cidr'             => 'ip_cidr',
            'event_ip.data_center'      => 'ip_data_center',
            'event_ip.tor'              => 'ip_tor',
            'event_ip.vpn'              => 'ip_vpn',
            'event_ip.starlink'         => 'ip_starlink',
            'event_ip.blocklist'        => 'ip_blocklist',
            'event_ip.relay'            => 'ip_relay',
            'event_ip.checked'          => 'ip_checked',
            'event_ip.shared'           => 'ip_shared',
            'event_ip.fraud_detected'   => 'ip_fraud_detected',
            'event_ip.total_visit'      => 'ip_total_visit',
            'event_ip.lastseen'         => 'ip_lastseen',
            'event_ip.created'          => 'ip_created',
            'event_ip.updated'          => 'ip_updated',

            'event_isp.id'              => 'isp_id',
            'event_isp.asn'             => 'isp_asn',
            'event_isp.name'            => 'isp_name',
            'event_isp.description'     => 'isp_description',
            'event_isp.total_ip'        => 'isp_total_ip',
            'event_isp.total_visit'     => 'isp_total_visit',
            'event_isp.total_account'   => 'isp_total_account',
            'event_isp.lastseen'        => 'isp_lastseen',
            'event_isp.created'         => 'isp_created',
            'event_isp.updated'         => 'isp_updated',

            'countries.id'              => 'ip_country_id',
            'countries.value'           => 'ip_country_name',
            'countries.iso'             => 'ip_country_iso',

            'event_country.id'              => 'country_data_id',
            'event_country.total_visit'     => 'country_total_visit',
            'event_country.total_ip'        => 'country_total_ip',
            'event_country.total_account'   => 'country_total_account',
            'event_country.lastseen'        => 'country_lastseen',
            'event_country.created'         => 'country_created',
            'event_country.updated'         => 'country_updated',

            'event_session.id'              => 'session_id',
            'event_session.account_id'      => 'session_account_id',
            'event_session.total_visit'     => 'session_total_visit',
            'event_session.total_device'    => 'session_total_device',
            'event_session.total_ip'        => 'session_total_ip',
            'event_session.total_country'   => 'session_total_country',
            'event_session.lastseen'        => 'session_lastseen',
            'event_session.created'         => 'session_created',
            'event_session.updated'         => 'session_updated',

            'event_url_query.id'        => 'url_query_id',
            'event_url_query.query'     => 'url_query_query',
            'event_url_query.lastseen'  => 'url_query_lastseen',
            'event_url_query.created'   => 'url_query_created',

            'event_url.id'              => 'url_id',
            'event_url.url'             => 'url_url',
            'event_url.title'           => 'url_title',
            'event_url.http_code'       => 'url_http_code',

            'event_url.total_visit'     => 'url_total_visit',
            'event_url.total_ip'        => 'url_total_ip',
            'event_url.total_device'    => 'url_total_device',
            'event_url.total_account'   => 'url_total_account',
            'event_url.total_country'   => 'url_total_country',
            'event_url.total_edit'      => 'url_total_edit',

            'event_url.lastseen'        => 'url_lastseen',
            'event_url.created'         => 'url_created',
            'event_url.updated'         => 'url_updated',

            'event_referer.id'          => 'referer_id',
            'event_referer.referer'     => 'referer_referer',
            'event_referer.lastseen'    => 'referer_lastseen',
            'event_referer.created'     => 'referer_created',

            'event_payload.id'          => 'payload_id',
            'event_payload.payload'     => 'payload_payload',
            'event_payload.created'     => 'payload_created',
        ];

        $this->join = [
            'event_email'       => 'LEFT JOIN event_email ON event_email.id = event.email',
            'event_phone'       => 'LEFT JOIN event_phone ON event_phone.id = event.phone',
            'event_device'      => 'LEFT JOIN event_device ON event_device.id = event.device',
            'event_ua_parsed'   => 'LEFT JOIN event_ua_parsed ON event_ua_parsed.id = event_device.ua',
            'event_ip'          => 'LEFT JOIN event_ip ON event_ip.id = event.ip',
            'event_country'     => 'LEFT JOIN event_country ON event_country.country = countries.id AND event_country.key = event_ip.key',
            'event_isp'         => 'LEFT JOIN event_isp ON event_isp.id = event_ip.isp',
            'countries'         => 'LEFT JOIN countries ON countries.id = event_ip.country',
            'event_session'     => 'LEFT JOIN event_session ON event_session.id = event.session_id',
            'event_url'         => 'LEFT JOIN event_url ON event_url.id = event.url',
            'event_url_query'   => 'LEFT JOIN event_url_query ON event_url_query.id = event.query',
            'event_referer'     => 'LEFT JOIN event_referer ON event_referer.id = event.referer',
            'event_payload'     => 'LEFT JOIN event_payload ON event_payload.id = event.payload',
            //'event_domain'  => 'LEFT JOIN event_domain ON event_email.domain = event_domain.id',
            //'countries'     => 'LEFT JOIN countries ON countries.id = event_phone.country_code',
            //'event_session' => 'LEFT JOIN event_session ON event_session.id = event_account.session_id',
        ];

        parent::__construct($key);
    }
}
