<?php

/**
 * tirreno ~ open security analytics
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

namespace Controllers\Admin\Events;

class Data extends \Controllers\Admin\Base\Data {
    public function getList(int $apiKey): array {
        $result = [];
        $model = new \Models\Grid\Events\Grid($apiKey);

        $map = [
            'ipId'          => 'getEventsByIpId',
            'ispId'         => 'getEventsByIspId',
            'userId'        => 'getEventsByUserId',
            'botId'         => 'getEventsByDeviceId',
            'domainId'      => 'getEventsByDomainId',
            'countryId'     => 'getEventsByCountryId',
            'resourceId'    => 'getEventsByResourceId',
            'fieldId'       => 'getEventsByFieldId',
        ];

        $result = $this->idMapIterate($map, $model, 'getAllEvents');

        return $result;
    }

    public function getEventDetails(int $eventId, int $apiKey): array {
        $result = (new \Models\Event())->getEventDetails($eventId, $apiKey);

        $tsColumns = ['device_created', 'latest_decision', 'added_to_review', 'score_updated_at', 'event_time'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }

    public function getAllEventTypes(): array {
        return (new \Models\EventType())->getAll();
    }

    public function getAllDeviceTypes(): array {
        return \Utils\Constants::get('DEVICE_TYPES');
    }

    public function extendPayload(array $data, int $apiKey): array {
        if (isset($data['event_type_id']) && isset($data['id'])) {
            $payloadTypes = [\Utils\Constants::get('PAGE_SEARCH_EVENT_TYPE_ID'), \Utils\Constants::get('ACCOUNT_EMAIL_CHANGE_EVENT_TYPE_ID')];
            if ($data['event_type_id'] === \Utils\Constants::get('FIELD_EDIT_EVENT_TYPE_ID')) {
                $model = new \Models\FieldAuditTrail();
                $data['event_payload'] = json_encode($model->getByEventId($data['id'], $apiKey));
            } elseif (in_array($data['event_type_id'], $payloadTypes)) {
                $model = new \Models\Payload();
                $data['event_payload'] = $model->getByEventId($data['id'], $apiKey);
            }
        }

        return $data;
    }
}
