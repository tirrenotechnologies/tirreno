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

namespace Models\Chart;

class Blacklist extends Base {
    public function getData(int $apiKey): array {
        $data = $this->getFirstLine($apiKey);

        $timestamps = array_column($data, 'ts');
        $line1      = array_column($data, 'ts_new_records');

        return $this->addEmptyDays([$timestamps, $line1]);
    }

    private function getFirstLine(int $apiKey): array {
        $query = (
            'SELECT
                EXTRACT(EPOCH FROM date_trunc(:resolution, tbl.created + :offset))::bigint AS ts,
                COUNT(*) AS ts_new_records
            FROM (
                SELECT DISTINCT
                    blacklist.accountid,
                    blacklist.created,
                    extra.type,
                    CASE extra.type
                        WHEN \'ip\'    THEN blacklist.ip
                        WHEN \'email\' THEN blacklist.email
                        WHEN \'phone\' THEN blacklist.phone
                    END AS value

                FROM
                    (
                    SELECT
                        event_account.id                AS accountid,
                        event_account.latest_decision   AS created,
                        CASE WHEN event_ip.fraud_detected THEN split_part(event_ip.ip::text, \'/\', 1) ELSE NULL END AS ip,
                        event_ip.fraud_detected AS ip_fraud,
                        CASE WHEN event_email.fraud_detected THEN event_email.email ELSE NULL END AS email,
                        event_email.fraud_detected AS email_fraud,
                        CASE WHEN event_phone.fraud_detected THEN event_phone.phone_number ELSE NULL END AS phone,
                        event_phone.fraud_detected AS phone_fraud
                    FROM event

                    LEFT JOIN event_account
                    ON event_account.id = event.account

                    LEFT JOIN event_ip
                    ON event_ip.id = event.ip

                    LEFT JOIN event_email
                    ON event_email.id = event.email

                    LEFT JOIN event_phone
                    ON event_phone.id = event.phone

                    WHERE
                        event_account.key = :api_key AND
                        event_account.fraud IS TRUE AND
                        event_account.latest_decision >= :start_time AND
                        event_account.latest_decision <= :end_time AND
                        (
                            event_email.fraud_detected IS TRUE OR
                            event_ip.fraud_detected IS TRUE OR
                            event_phone.fraud_detected IS TRUE
                        )
                    ) AS blacklist,
                    LATERAL (
                        VALUES
                            (CASE WHEN ip_fraud = true THEN \'ip\' END),
                            (CASE WHEN email_fraud = true THEN \'email\' END),
                            (CASE WHEN phone_fraud = true THEN \'phone\' END)
                    ) AS extra(type)

                WHERE
                    extra.type IS NOT NULL
            ) AS tbl
            GROUP BY ts
            ORDER BY ts'
        );

        return $this->execute($query, $apiKey);
    }
}
