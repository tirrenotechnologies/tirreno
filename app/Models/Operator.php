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

class Operator extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'dshb_operators';

    public function insertRecord(?string $password, string $email, string $timezone): int {
        $params = [
            ':password' => $password ? \Tirreno\Utils\Access::hashPassword($password) : $password,
            ':email'    => $email,
            ':timezone' => $timezone,
            ':active'   => 1,
        ];

        $query = (
            'INSERT INTO dshb_operators (
                password, email, timezone, is_active
            ) VALUES (
                :password, :email, :timezone, :active
            ) RETURNING id'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'];
    }

    public function updatePassword(string $password, int $operatorId): void {
        $params = [
            ':password'     => \Tirreno\Utils\Access::hashPassword($password),
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                password = :password
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function updateEmail(string $email, int $operatorId): void {
        $params = [
            ':email'        => $email,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                email = :email
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function updateTimezone(string $timezone, int $operatorId): void {
        $params = [
            ':timezone'     => $timezone,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                timezone = :timezone
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function updateNotificationPreferences(string $reminder, int $operatorId): void {
        $params = [
            ':reminder'     => $reminder,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                unreviewed_items_reminder_freq = :reminder
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function updateReviewedQueueCnt(int $cnt, int $operatorId): void {
        $params = [
            ':cnt'          => $cnt,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                review_queue_cnt = :cnt,
                review_queue_updated_at = NOW()
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function updateBlacklistUsersCnt(int $cnt, int $operatorId): void {
        $params = [
            ':cnt'          => $cnt,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                blacklist_users_cnt = :cnt
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function updateLastEventTime(string $timestamp, int $operatorId): void {
        $params = [
            ':timestamp'    => $timestamp,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                last_event_time = :timestamp
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function closeAccount(int $operatorId): void {
        $params = [
            ':closed'       => 1,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                is_closed = :closed
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function deleteAccount(int $operatorId): void {
        $params = [
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'DELETE FROM dshb_operators
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function removeData(int $operatorId): void {
        $params = [
            ':operator_id' => $operatorId,
        ];

        # firstly delete all nested data to not break the cascade
        $queries = [
            'DELETE FROM event
            WHERE event.key IN (SELECT id FROM dshb_api WHERE creator = :operator_id);',
            'DELETE FROM event_account
            WHERE event_account.key IN (SELECT id FROM dshb_api WHERE creator = :operator_id);',
            'DELETE FROM event_ip
            WHERE event_ip.key IN (SELECT id FROM dshb_api WHERE creator = :operator_id);',
            'DELETE FROM event_device
            WHERE event_device.key IN (SELECT id FROM dshb_api WHERE creator = :operator_id);',
            'DELETE FROM event_email
            WHERE event_email.key IN (SELECT id FROM dshb_api WHERE creator = :operator_id);',
        ];

        try {
            $this->db->begin();
            $this->db->exec($queries, array_fill(0, 5, $params));

            $query = 'DELETE FROM dshb_api WHERE creator = :operator_id';
            $this->db->exec($query, $params);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function activateByOperatorId(int $operatorId): void {
        $params = [
            ':active'       => 1,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'UPDATE dshb_operators
            SET
                is_active = :active
            WHERE
                dshb_operators.id = :operator_id'
        );

        $this->execQuery($query, $params);
    }

    public function getByEmail(string $email): array {
        $params = [
            ':email'  => $email,
        ];

        $query = (
            'SELECT
                id
            FROM
                dshb_operators
            WHERE
                LOWER(dshb_operators.email) = LOWER(:email)'
        );

        return $this->execQuery($query, $params);
    }

    public function getActivatedByEmail(string $email): ?int {
        $params = [
            ':email'    => $email,
            ':active'   => 1,
            ':closed'   => 0,
        ];

        $query = (
            'SELECT
                id
            FROM
                dshb_operators
            WHERE
                LOWER(dshb_operators.email) = LOWER(:email) AND
                dshb_operators.is_active = :active AND
                dshb_operators.is_closed = :closed'
        );

        $results = $this->execQuery($query, $params);

        return $results[0]['id'] ?? null;
    }

    public function getOperatorById(int $operatorId): array {
        $params = [
            ':closed'       => 0,
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'SELECT
                id,
                email,
                password,
                firstname,
                lastname,
                activation_key,
                timezone,
                review_queue_cnt,
                review_queue_updated_at,
                unreviewed_items_reminder_freq,
                last_event_time,
                blacklist_users_cnt
            FROM
                dshb_operators
            WHERE
                dshb_operators.id = :operator_id AND
                dshb_operators.is_closed = :closed'
        );

        $results = $this->execQuery($query, $params);

        return $results[0] ?? [];
    }

    public function verifyPassword(string $password, int $operatorId): bool {
        $params = [
            ':operator_id'  => $operatorId,
        ];

        $query = (
            'SELECT
                password
            FROM
                dshb_operators
            WHERE
                dshb_operators.id = :operator_id'
        );

        $results = $this->execQuery($query, $params);

        $operatorPassword = $results[0]['password'] ?? null;

        if (!$results || !$operatorPassword) {
            return false;
        }

        return \Tirreno\Utils\Access::verifyPassword($password, $operatorPassword);
    }

    public function getAll(): array {
        $query = (
            'SELECT
                id,
                email,
                firstname,
                lastname,
                last_event_time,
                review_queue_cnt
            FROM
                dshb_operators
            ORDER BY email ASC'
        );

        return $this->execQuery($query, null);
    }
}
