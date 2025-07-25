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

namespace Models\Context;

class Session extends Base {
    public function getContext(array $accountIds, int $apiKey, int $timezoneOffset = 0): array {
        $records = $this->getDetails($accountIds, $apiKey, $timezoneOffset);
        // one record per account
        $recordsByAccount = $this->groupRecordsByAccount($records);

        return $recordsByAccount;
    }

    protected function getDetails(array $accountIds, int $apiKey, int $timezoneOffset = 0): array {
        [$params, $placeHolders] = $this->getRequestParams($accountIds, $apiKey);

        $params[':night_start'] = gmdate('H:i:s', \Utils\Constants::get('NIGHT_RANGE_SECONDS_START') - $timezoneOffset);
        $params[':night_end'] = gmdate('H:i:s', \Utils\Constants::get('NIGHT_RANGE_SECONDS_END') - $timezoneOffset);

        // boolean logic for defining time ranges overlap
        $query = (
            "SELECT
                event_session.account_id                        AS accountid,
                BOOL_OR(event_session.total_country > 1)        AS event_session_multiple_country,
                BOOL_OR(event_session.total_ip > 1)             AS event_session_multiple_ip,
                BOOL_OR(event_session.total_device > 1)         AS event_session_multiple_device,
                BOOL_OR(
                    (event_session.lastseen - event_session.created) > INTERVAL '1 day' OR
                    (
                        CASE WHEN :night_start::time < :night_end::time
                        THEN
                            (event_session.lastseen::time >= :night_start::time AND event_session.lastseen::time <= :night_end::time) OR
                            (event_session.created::time >= :night_start::time AND event_session.created::time <= :night_end::time) OR
                            (
                                CASE WHEN event_session.lastseen::time > event_session.created::time
                                THEN
                                    event_session.total_visit > 1 AND :night_start::time >= event_session.created::time AND :night_start::time <= event_session.lastseen::time
                                ELSE
                                    event_session.total_visit > 1 AND (:night_start::time >= event_session.created::time OR :night_start::time <= event_session.lastseen::time)
                                END
                            )
                        ELSE
                            event_session.lastseen::time >= :night_start::time OR event_session.lastseen::time <= :night_end::time OR
                            event_session.created::time >= :night_start::time OR event_session.created::time <= :night_end::time OR
                            event_session.lastseen::time < event_session.created::time
                        END
                )) AS event_session_night_time
            FROM
                event_session
            WHERE
                event_session.key = :api_key AND
                event_session.account_id IN ({$placeHolders})
            GROUP BY event_session.account_id"
        );

        return $this->execQuery($query, $params);
    }
}
