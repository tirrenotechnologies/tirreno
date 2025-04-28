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

namespace Controllers\Api;

class Blacklist extends Endpoint {
    public function search(): void {
        $value = $this->getBodyProp('value', 'string');

        $model = new \Models\BlacklistItems();
        $itemFound = $model->searchBlacklistedItem($value, $this->apiKey->id);

        $this->data = [
            'value'         => $value,
            'blacklisted'   => $itemFound,
        ];
    }
}
