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

namespace Models\Api;

class Users extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_account';

    public function getUsersByApiKey(?int $userId, int $apiKey): array {
        $params = [
            ':api_key' => $apiKey,
            ':user_url' => \Utils\Variables::getHostWithProtocolAndBase() . '/id/',
        ];

        $query = (
            'SELECT
                -- create user url
                :user_url || event_account.id AS internal_url,
                -- -event_account.id,
                event_account.userid,
                event_account.created,
                -- event_account.key,
                event_account.lastip,
                event_account.lastemail,
                event_account.lastphone,
                event_account.lastseen,
                event_account.fullname,
                event_account.firstname,
                event_account.lastname,
                -- event_account.is_important,
                event_account.total_visit,
                event_account.total_country,
                event_account.total_ip,
                event_account.total_device,
                event_account.total_shared_ip,
                event_account.total_shared_phone,
                event_account.score_updated_at,
                event_account.score,
                event_account.score_details,
                event_account.reviewed,
                event_account.fraud,
                event_account.latest_decision

            FROM event_account'
        );

        $where = ' WHERE key = :api_key';

        if ($userId !== null) {
            $params[':user_id'] = $userId;
            $where .= ' AND event_account.id = :user_id';
        }

        $query .= $where;

        return $this->execQuery($query, $params);
    }
}
