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

class Sessions extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event_session';
        $this->model = 'sessions';

        $this->fields = [
            'event_session.id'              => 'session_id',
            'event_session.account_id'      => 'session_account_id',
            'event_session.total_visit'     => 'session_total_visit',
            'event_session.total_device'    => 'session_total_device',
            'event_session.total_ip'        => 'session_total_ip',
            'event_session.total_country'   => 'session_total_country',
            'event_session.lastseen'        => 'session_lastseen',
            'event_session.created'         => 'session_created',
            'event_session.updated'         => 'session_updated',
        ];

        parent::__construct($key);
    }
}
