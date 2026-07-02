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

class Devices extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event_device';
        $this->model = 'devices';

        $this->fields = [
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
        ];

        $this->join = [
            'event_ua_parsed' => 'LEFT JOIN event_ua_parsed ON event_ua_parsed.id = event_device.ua',
        ];

        parent::__construct($key);
    }
}
