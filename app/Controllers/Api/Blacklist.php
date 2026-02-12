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

namespace Tirreno\Controllers\Api;

class Blacklist extends Endpoint {
    public function search(): void {
        $value = $this->getBodyProp('value', 'string');

        $model = new \Tirreno\Models\BlacklistItems();
        $itemFound = $model->searchBlacklistedItem($value, $this->apiKeyId);

        $this->data = [
            'value'         => $value,
            'blacklisted'   => $itemFound,
        ];
    }
}
