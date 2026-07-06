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

class Users extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event_account';
        $this->model = 'users';

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
        ];

        $this->join = [
            'event_email'   => 'LEFT JOIN event_email ON event_email.id = event_account.lastemail',
            'event_phone'   => 'LEFT JOIN event_phone ON event_phone.id = event_account.lastphone',
            //'event_domain'  => 'LEFT JOIN event_domain ON event_email.domain = event_domain.id',
            //'countries'     => 'LEFT JOIN countries ON countries.id = event_phone.country_code',
            //'event_session' => 'LEFT JOIN event_session ON event_session.id = event_account.session_id',
        ];

        parent::__construct($key);
    }
}
