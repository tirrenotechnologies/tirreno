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

class Payloads extends \Tirreno\Models\Query\Base {
    public function __construct(int $key) {
        $this->table = 'event_payload';
        $this->model = 'payloads';

        $this->fields = [
            'event_payload.id'          => 'payload_id',
            'event_payload.payload'     => 'payload_payload',
            'event_payload.created'     => 'payload_created',
        ];

        parent::__construct($key);
    }
}
