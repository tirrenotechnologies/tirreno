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

namespace Controllers\Admin\Events;

class Data extends \Controllers\Base {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Models\Grid\Events\Grid($apiKey);

        $ipId = $this->f3->get('REQUEST.ipId');
        $ispId = $this->f3->get('REQUEST.ispId');
        $userId = $this->f3->get('REQUEST.userId');
        $botId = $this->f3->get('REQUEST.botId');
        $domainId = $this->f3->get('REQUEST.domainId');
        $countryId = $this->f3->get('REQUEST.countryId');
        $resourceId = $this->f3->get('REQUEST.resourceId');

        if (isset($ipId) && is_numeric($ipId)) {
            $result = $model->getEventsByIpId($ipId);
        }

        if (isset($ispId)) {
            $result = $model->getEventsByIspId($ispId);
        }

        if (isset($domainId) && is_numeric($domainId)) {
            $result = $model->getEventsByDomainId($domainId);
        }

        if (isset($userId) && is_numeric($userId)) {
            $result = $model->getEventsByUserId($userId);
        }

        if (isset($countryId) && is_numeric($countryId)) {
            $result = $model->getEventsByCountryId($countryId);
        }

        if (isset($resourceId) && is_numeric($resourceId)) {
            $result = $model->getEventsByResourceId($resourceId);
        }

        if (isset($botId) && is_numeric($botId)) {
            $result = $model->getEventsByDeviceId($botId);
        }

        if (!$result) {
            $result = $model->getAllEvents();
        }

        return $result;
    }

    public function getEventDetails(int $apiKey): array {
        $params = $this->f3->get('GET');
        $id = $params['id'];
        $model = new \Models\Event();

        $result = $model->getEventDetails($id, $apiKey);

        $tsColumns = ['device_created', 'latest_decision', 'added_to_review', 'score_updated_at', 'event_time'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }

    public function getAllEventTypes(): array {
        $model = new \Models\EventType();

        return $model->getAll();
    }
}
