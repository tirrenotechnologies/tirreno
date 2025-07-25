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

namespace Controllers\Admin\Emails;

class Data extends \Controllers\Base {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Models\Grid\Emails\Grid($apiKey);

        $userId = $this->f3->get('REQUEST.userId');

        if (isset($userId) && is_numeric($userId)) {
            $result = $model->getEmailsByUserId($userId);
        }

        return $result;
    }

    public function getEmailDetails(int $apiKey): array {
        $params = $this->f3->get('GET');
        $id = $params['id'];
        $model = new \Models\Email();

        $details = $model->getEmailDetails($id, $apiKey);
        $details['enrichable'] = $this->isEnrichable($apiKey);

        $tsColumns = ['email_created', 'email_lastseen', 'domain_lastseen', 'domain_created'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $details);

        return $details;
    }

    private function isEnrichable(int $apiKey): bool {
        $model = new \Models\ApiKeys();

        return $model->attributeIsEnrichable('email', $apiKey);
    }
}
