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

namespace Tirreno\Models;

class UserScore extends \Tirreno\Models\Base {
    protected string $tableName = 'event_account_score';

    public function updateUserScore(array $scores, array $details, int $accountId, int $apiKey): void {
        $data = [];
        $cnt = 0;
        foreach ($scores as $setId => $score) {
            $postfix = strval($cnt);
            $cnt += 1;
            $data[] = [
                ':set_id_' . $postfix   => $setId,
                ':score_' . $postfix    => $score,
                ':details_' . $postfix  => json_encode($details[$setId]),
            ];
        }

        $params = array_merge(...$data);
        $params[':user_id'] = $accountId;
        $params[':api_key'] = $apiKey;

        tirreno('log')->debug('score update for account %d; scores -- %s.', $accountId, json_encode($scores));
        tirreno('log')->debug('score update for account %d; details -- %s.', $accountId, json_encode($details));

        $parts = [];
        foreach ($data as $part) {
            $parts[] = '(:user_id, ' . implode(', ', array_keys($part)) . ', :api_key, NOW())';
        }

        $values = implode(', ', $parts);

        $query = (
            "INSERT INTO event_account_score
                (account, set, score, rules, key, lastseen)
            VALUES
                {$values}
            ON CONFLICT (account, set) DO UPDATE
            SET
                score = EXCLUDED.score,
                rules = EXCLUDED.rules"
        );

        $this->execQuery($query, $params);
    }

    public function getScoreByUserId(int $accountId, int $apiKey): array {
        $params = [
            ':user_id'  => $accountId,
            ':api_key'  => $apiKey,
        ];

        $query = (
            'SELECT
                event_account_score.id,
                event_account_score.account,
                event_account_score.set,
                event_account_score.score,
                event_account_score.rules,
                event_account_score.lastseen,
                event_account_score.created,
            FROM
                event_account_score
            WHERE
                event_account_score.account = :user_id AND
                event_account_score.key = :api_key'
        );

        return $this->execQuery($query, $params);
    }

    public function getScoreDetailsByUserId(int $id, int $apiKey, bool $all = false): array {
        $params = [
            ':account_id' => $id,
            ':api_key' => $apiKey,
        ];

        $query = (
            "SELECT
                (score_element ->> 'score')::int    AS score,
                event_account_score.score           AS total_score,
                event_account_score.set,
                dshb_rules.uid,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.validated,
                dshb_rules.attributes

            FROM
                event_account_score

            JOIN jsonb_array_elements(event_account_score.rules) AS score_element
            ON true

            LEFT JOIN dshb_rules
            ON (dshb_rules.uid = (score_element ->> 'uid')::varchar)

            WHERE
                event_account_score.account = :account_id AND
                event_account_score.key = :api_key AND
                uid IS NOT NULL"
        );

        if (!$all) {
            $query .= ' AND (score_element ->> \'score\')::int != 0';
        }

        $results = $this->execQuery($query, $params);

        usort($results, [\Tirreno\Utils\Sort::class, 'cmpSetUid']);

        return $results;
    }
}
