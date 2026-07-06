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

class Countries extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event_country';
        $this->model = 'countries';

        $this->fields = [
            'countries.id'              => 'country_id',
            'countries.value'           => 'country_name',
            'countries.iso'             => 'country_iso',

            'event_country.id'              => 'country_data_id',
            'event_country.total_visit'     => 'country_total_visit',
            'event_country.total_ip'        => 'country_total_ip',
            'event_country.total_account'   => 'country_total_account',
            'event_country.lastseen'        => 'country_lastseen',
            'event_country.created'         => 'country_created',
            'event_country.updated'         => 'country_updated',
        ];

        $this->join = [
            'countries' => 'LEFT JOIN countries ON event_country.country = countries.id',
        ];

        parent::__construct($key);
    }
}
