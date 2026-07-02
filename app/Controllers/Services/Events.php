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

namespace Tirreno\Controllers\Services;

class Events extends \Tirreno\Controllers\Services\Base {
    public function getList(int $apiKey): array {
        $map = [
            'ipId'          => 'getEventsByIpId',
            'ispId'         => 'getEventsByIspId',
            'userId'        => 'getEventsByUserId',
            'userAgentId'   => 'getEventsByDeviceId',
            'domainId'      => 'getEventsByDomainId',
            'countryId'     => 'getEventsByCountryId',
            'resourceId'    => 'getEventsByResourceId',
            'fieldId'       => 'getEventsByFieldId',
        ];

        return $this->idMapIterate($map, tirreno('grids')->events, $apiKey);
    }

    public function getChart(string $mode, int $apiKey): array {
        if (!in_array($mode, tirreno('utils')->constants->EVENTS_CHARTS)) {
            $mode = 'events';
        }

        return tirreno('charts')->$mode->getData($apiKey);
    }

    public function getEventDetails(int $eventId, int $apiKey): array {
        $result = tirreno('models')->event->getEventDetails($eventId, $apiKey);

        $tsColumns = ['device_created', 'latest_decision', 'added_to_review', 'score_updated_at', 'event_time'];
        $result = tirreno('utils')->timezones->localizeTimestampsForActiveOperator($tsColumns, $result);

        return $result;
    }

    public function getAllEventTypes(): array {
        return tirreno('models')->eventType->getAll();
    }

    public function getAllDeviceTypes(): array {
        return tirreno('utils')->constants->DEVICE_TYPES;
    }

    public function extendPayload(array $data, int $apiKey): array {
        if (isset($data['event_type_id']) && isset($data['id'])) {
            $payloadTypes = [tirreno('utils')->constants->PAGE_SEARCH_EVENT_TYPE_ID, tirreno('utils')->constants->ACCOUNT_EMAIL_CHANGE_EVENT_TYPE_ID];
            if ($data['event_type_id'] === tirreno('utils')->constants->FIELD_EDIT_EVENT_TYPE_ID) {
                $data['event_payload'] = json_encode(tirreno('models')->fieldAuditTrail->getByEventId($data['id'], $apiKey));
            } elseif (in_array($data['event_type_id'], $payloadTypes)) {
                $data['event_payload'] = tirreno('models')->payload->getByEventId($data['id'], $apiKey);
            }
        }

        return $data;
    }
}
