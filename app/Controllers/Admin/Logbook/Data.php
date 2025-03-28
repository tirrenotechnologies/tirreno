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

namespace Controllers\Admin\Logbook;

class Data extends \Controllers\Base {
    public function getList(int $apiKey): array {
        $model = new \Models\Grid\Logbook\Grid($apiKey);

        return $model->getAllLogbookEvents();
    }

    public function getLogbookDetails(int $apiKey): array {
        $params = $this->f3->get('GET');
        $id = $params['id'];
        $model = new \Models\Logbook();

        return $model->getLogbookDetails($id, $apiKey);
    }
}
