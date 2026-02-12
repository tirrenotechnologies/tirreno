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

namespace Tirreno\Models\UserDetails;

class Behaviour extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event';

    public function getDayDetails(int $userId, array $dateRange, int $apiKey): array {
        $params = [
            ':user_id'          => $userId,
            ':api_key'          => $apiKey,
            ':start_ts'         => $dateRange['startDate'],
            ':end_ts'           => $dateRange['endDate'],
            ':offset'           => $dateRange['offset'],
            ':failed_login'     => \Tirreno\Utils\Constants::get()->ACCOUNT_LOGIN_FAIL_EVENT_TYPE_ID,
            ':success_login'    => \Tirreno\Utils\Constants::get()->ACCOUNT_LOGIN_EVENT_TYPE_ID,
            ':password_reset'   => \Tirreno\Utils\Constants::get()->ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID,
            ':seconds_day'      => \Tirreno\Utils\Constants::get()->SECONDS_IN_DAY,
            ':night_time_end'   => \Tirreno\Utils\Constants::get()->SECONDS_IN_HOUR * 5,
        ];

        $query = (
            'SELECT
                COUNT(CASE WHEN event.type = :failed_login THEN TRUE END)                       AS failed_login_cnt,
                COUNT(CASE WHEN event.type = :password_reset THEN TRUE END)                     AS password_reset_cnt,
                COUNT(CASE WHEN event.http_code > 400 THEN TRUE END)                            AS auth_error_cnt,
                COUNT(CASE WHEN event.type IN (:failed_login, :success_login) THEN TRUE END)    AS login_cnt,
                COUNT(CASE WHEN
                    MOD(EXTRACT(EPOCH FROM event.time) + :offset, :seconds_day) < :night_time_end THEN TRUE END
                )                                   AS off_hours_login_cnt,
                COUNT(DISTINCT event.device)        AS device_cnt,
                COUNT(DISTINCT event.ip)            AS ip_cnt,
                COUNT(DISTINCT event.session_id)    AS session_cnt

            FROM
                event

            WHERE
                event.account = :user_id AND
                event.key = :api_key AND
                event.time > :start_ts AND
                event.time < :end_ts'
        );

        $results = $this->execQuery($query, $params);

        $result = $results[0] ?? [];

        if ($result) {
            $params = [
                ':user_id'  => $userId,
                ':api_key'  => $apiKey,
                ':start_ts' => $dateRange['startDate'],
                ':end_ts'   => $dateRange['endDate'],
            ];

            $query = (
                'SELECT
                    COALESCE(AVG(event_session.total_visit), 0)::int    AS avg_event_cnt
                FROM
                    event_session

                WHERE
                    event_session.account_id = :user_id AND
                    event_session.key = :api_key AND
                    event_session.lastseen > :start_ts AND
                    event_session.lastseen < :end_ts'
            );

            $results = $this->execQuery($query, $params);

            $result['avg_event_cnt'] = $results[0]['avg_event_cnt'] ?? 0;
        }

        return $result;
    }
}
