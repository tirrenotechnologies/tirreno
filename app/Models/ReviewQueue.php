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

namespace Models;

class ReviewQueue extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_account';

    public function getCountShort(int $apiKey): int {
        $params = [
            ':api_key'  => $apiKey,
            ':limit'    => \Utils\Constants::get('REVIEW_QUEUE_TILE_LIMIT'),
        ];

        $query = (
            'SELECT
                COUNT(*) AS count
            FROM (
                SELECT
                    event_account.id

                FROM
                    event_account

                WHERE
                    event_account.key = :api_key AND
                    event_account.fraud IS NULL AND
                    event_account.added_to_review IS NOT NULL
                LIMIT :limit
            ) as t'
        );

        return $this->execQuery($query, $params)[0]['count'] ?? 0;
    }
}
