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

class FieldAuditTrails extends Base {
    protected $DB_TABLE_NAME = 'event_field_audit_trail';

    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $timestamps = array_column($data, 'ts');
        $line1      = array_column($data, 'total_count');

        return $this->addEmptyDays([$timestamps, $line1]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, event_field_audit_trail.created + :offset))::bigint AS ts,
                COUNT(DISTINCT event_field_audit_trail.id) AS total_count
            FROM
                event_field_audit_trail

            WHERE
                event_field_audit_trail.key = :api_key AND
                event_field_audit_trail.created >= :start_time AND
                event_field_audit_trail.created <= :end_time

            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }
}
