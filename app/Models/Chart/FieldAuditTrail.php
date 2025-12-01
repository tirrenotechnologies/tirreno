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

namespace Models\Chart;

class FieldAuditTrail extends BaseEventsCount {
    public function getCounts(int $apiKey): array {
        $query = (
            "SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_field_audit_trail.created + :offset))::bigint AS ts,
                0                                                   AS event_normal_type_count,
                COUNT(DISTINCT event_field_audit_trail.event_id)    AS event_editing_type_count,
                0                                                   AS event_alert_type_count

            FROM
                event_field_audit_trail

            WHERE
                event_field_audit_trail.field_id = :id AND
                event_field_audit_trail.key = :api_key AND
                event_field_audit_trail.created >= :start_time AND
                event_field_audit_trail.created <= :end_time

            GROUP BY ts
            ORDER BY ts"
        );

        return $this->executeOnRangeById($query, $apiKey);
    }

    protected function executeOnRangeById(string $query, int $apiKey): array {
        // do not use offset because :start_time/:end_time compared with UTC event.time
        $dateRange = \Utils\DateRange::getLatestNDatesRangeFromRequest(180);
        $offset = \Utils\TimeZones::getCurrentOperatorOffset();

        $params = [
            ':api_key'      => $apiKey,
            ':end_time'     => $dateRange['endDate'],
            ':start_time'   => $dateRange['startDate'],
            ':resolution'   => \Utils\DateRange::getResolutionFromRequest(),
            ':id'           => \Utils\Conversion::getIntRequestParam('id'),
            ':offset'       => strval($offset),     // str for postgres
        ];

        return $this->execQuery($query, $params);
    }
}
