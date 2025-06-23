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

namespace Models;

class User extends \Models\BaseSql implements \Interfaces\ApiKeyAccessAuthorizationInterface, \Interfaces\ApiKeyAccountAccessAuthorizationInterface {
    protected $DB_TABLE_NAME = 'event_account';

    public function checkAccess(int $userId, int $apiKey): bool {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                userid

            FROM
                event_account

            WHERE
                event_account.id = :user_id
                AND event_account.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function checkAccessByExternalId(string $externalUserId, int $apiKey): bool {
        $params = [
            ':user_id' => $externalUserId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                userid

            FROM
                event_account

            WHERE
                event_account.userid = :user_id
                AND event_account.key = :api_key

            LIMIT 1'
        );

        $results = $this->execQuery($query, $params);

        return count($results) > 0;
    }

    public function getUser(int $userId, int $apiKey): array {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        $query = (
            'SELECT
                event_account.id AS accountid,
                event_account.userid,
                event_account.lastseen,
                event_account.created,
                event_account.firstname,
                event_account.lastname,
                event_account.score,
                event_account.score_details,
                event_account.score_updated_at,
                event_account.is_important,
                event_account.fraud,
                event_account.reviewed,
                event_account.latest_decision,

                event_email.email

            FROM
                event_account

            LEFT JOIN event_email
            ON (event_account.lastemail = event_email.id)

            WHERE
                event_account.id = :user_id
                AND event_account.key = :api_key'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function deleteAllUserData(int $userId, int $apiKey): void {
        $params = [
            ':user_id' => $userId,
            ':api_key' => $apiKey,
        ];

        $queries = [
            // Delete all user events.
            'DELETE FROM event
            WHERE event.account = :user_id
                AND event.key = :api_key;',
            // Delete user account.
            'DELETE FROM event_account
            WHERE event_account.id = :user_id
                AND event_account.key = :api_key;',
            // Delete all devices related to user.
            'DELETE FROM event_device
            WHERE event_device.account_id = :user_id
                AND event_device.key = :api_key;',
            // Delete all emails related to user.
            'DELETE FROM event_email
            WHERE event_email.account_id = :user_id
                AND event_email.key = :api_key;',
            // Delete all phones related to user.
            'DELETE FROM event_phone
            WHERE event_phone.account_id = :user_id
                AND event_phone.key = :api_key;',
            // Delete all related sessions
            'DELETE FROM event_session
            WHERE event_session.account_id = :user_id
                AND event_session.key = :api_key',
        ];

        try {
            $model = new \Models\Events();
            $entities = $model->uniqueEntitesByUserId($userId, $apiKey);

            $this->db->begin();
            $this->db->exec($queries, array_fill(0, 6, $params));

            // force update totals for ips before isps and countries!
            $model = new \Models\Ip();
            $model->updateTotalsByEntityIds($entities['ip_ids'], $apiKey, true);

            $model = new \Models\Isp();
            $model->updateTotalsByEntityIds($entities['isp_ids'], $apiKey, true);

            $model = new \Models\Country();
            $model->updateTotalsByEntityIds($entities['country_ids'], $apiKey, true);

            $model = new \Models\Resource();
            $model->updateTotalsByEntityIds($entities['url_ids'], $apiKey, true);

            $model = new \Models\Domain();
            $model->updateTotalsByEntityIds($entities['domain_ids'], $apiKey, true);

            $model = new \Models\Phone();
            $model->updateTotalsByValues($entities['phone_numbers'], $apiKey, true);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function getTimeFrameTotal(array $ids, string $startDate, string $endDate, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;
        $params[':start_date'] = $startDate;
        $params[':end_date'] = $endDate;

        $query = (
            "SELECT
                event.account AS id,
                COUNT(*) AS cnt
            FROM event
            WHERE
                event.account IN ({$flatIds}) AND
                event.key = :key AND
                event.time > :start_date AND
                event.time < :end_date
            GROUP BY event.account"
        );

        $totalVisit = $this->execQuery($query, $params);

        $result = [];

        foreach ($ids as $id) {
            $result[$id] = ['total_visit' => 0];
        }

        foreach ($totalVisit as $rec) {
            $result[$rec['id']]['total_visit'] = $rec['cnt'];
        }

        return $result;
    }

    public function getAccountIdByUserId(string $userId, int $apiKey): self|null|false {
        $filters = [
            'key=? AND userid=?', $apiKey, $userId,
        ];

        return $this->load($filters);
    }

    public function getApplicableRulesByAccountId(int $id, int $apiKey): array {
        $params = [
            ':account_id' => $id,
            ':api_key' => $apiKey,
        ];

        $query = (
            "SELECT
                (score_element ->> 'score')::int    AS score,
                event_account.score                 AS total_score,
                dshb_rules.uid,
                dshb_rules.name,
                dshb_rules.descr,
                dshb_rules.validated

            FROM
                event_account

            JOIN jsonb_array_elements(event_account.score_details) AS score_element
            ON true

            LEFT JOIN dshb_rules
            ON (dshb_rules.uid = (score_element ->> 'uid')::varchar)

            WHERE
                event_account.id = :account_id AND
                event_account.key = :api_key AND
                (score_element ->> 'score')::int != 0 AND
                uid IS NOT NULL"
        );

        $results = $this->execQuery($query, $params);

        usort($results, static function ($a, $b): int {
            return $b['score'] <=> $a['score'];
        });

        return $results;
    }

    public function updateScoreDetails(array $data): void {
        $this->load(
            ['id=? AND key=?', $data['accountId'], $data['apiKey']],
        );

        if ($this->loaded()) {
            $this->score = $data['score'];
            $this->score_details = $data['details'];
            $this->score_updated_at = date('Y-m-d H:i:s');
            $this->score_recalculate = false;

            $this->save();
        }
    }

    public function updateFraudFlag(array $accountIds, int $apiKey, bool $fraud): void {
        if (!count($accountIds)) {
            return;
        }

        [$params, $placeHolders] = $this->getArrayPlaceholders($accountIds);

        $params[':fraud'] = $fraud;
        $params[':api_key'] = $apiKey;
        $params[':latest_decision'] = gmdate('Y-m-d H:i:s');

        $query = (
            "UPDATE event_account
                SET fraud = :fraud, latest_decision = :latest_decision

            WHERE
                key = :api_key
                AND id IN ({$placeHolders})"
        );

        $this->execQuery($query, $params);
    }

    public function updateReviewedFlag(int $accountId, int $apiKey, bool $reviewed): void {
        $this->load(
            ['id=? AND key=?', $accountId, $apiKey],
        );

        if ($this->loaded()) {
            //Workaround. Emulate nullable default value
            $this->fraud = null;

            $this->reviewed = $reviewed;

            $this->save();
        }
    }

    public function updateTotalsByAccountIds(array $ids, int $apiKey): int {
        if (!count($ids)) {
            return 0;
        }

        [$params, $flatIds] = $this->getArrayPlaceholders($ids);
        $params[':key'] = $apiKey;

        $query = (
            "UPDATE event_account
            SET
                total_visit = COALESCE(sub.total_visit, 0),
                total_ip = COALESCE(sub.total_ip, 0),
                total_device = COALESCE(sub.total_device, 0),
                total_country = COALESCE(sub.total_country, 0),
                total_shared_ip = COALESCE(sub.total_shared_ips, 0),
                total_shared_phone = COALESCE(sub.total_shared_phones, 0),
                updated = date_trunc('milliseconds', now())
            FROM (
                SELECT
                    event.account,
                    COUNT(*) AS total_visit,
                    COUNT(DISTINCT event.ip) AS total_ip,
                    COUNT(DISTINCT event.device) AS total_device,
                    COUNT(DISTINCT event_ip.country) AS total_country,
                    COUNT(DISTINCT CASE WHEN event_ip.shared > 1 THEN event.ip ELSE NULL END) AS total_shared_ips,
                    (SELECT COUNT(*) FROM event_phone WHERE event_phone.account_id = event.account AND event_phone.shared > 1) AS total_shared_phones
                FROM event
                LEFT JOIN event_ip
                ON event_ip.id = event.ip
                WHERE event.account IN ($flatIds)
                GROUP BY event.account
            ) AS sub
            RIGHT JOIN event_account sub_account ON sub.account = sub_account.id
            WHERE
                event_account.id = sub_account.id AND
                event_account.id IN ($flatIds) AND
                event_account.key = :key AND
                event_account.lastseen >= event_account.updated"
        );

        return $this->execQuery($query, $params);
    }

    public function refreshTotals(array $res, int $apiKey): array {
        [$params, $flatIds] = $this->getArrayPlaceholders(array_column($res, 'id'));
        $params[':key'] = $apiKey;
        $query = (
            "SELECT
                id,
                total_ip,
                total_visit,
                total_device,
                total_country
            FROM event_account
            WHERE id IN ({$flatIds}) AND key = :key"
        );

        $result = $this->execQuery($query, $params);

        $indexedResult = [];
        foreach ($result as $item) {
            $indexedResult[$item['id']] = $item;
        }

        foreach ($res as $idx => $item) {
            $item['total_ip'] = $indexedResult[$item['id']]['total_ip'];
            $item['total_visit'] = $indexedResult[$item['id']]['total_visit'];
            $item['total_device'] = $indexedResult[$item['id']]['total_device'];
            $item['total_country'] = $indexedResult[$item['id']]['total_country'];
            $res[$idx] = $item;
        }

        return $res;
    }
}
