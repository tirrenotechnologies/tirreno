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

class Operator extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_operators';

    public function add(array $data): void {
        $password = $data['password'] ?? null;

        if ($password) {
            $this->password = self::hashPassword($password);
        }

        $this->email = $data['email'];
        $this->timezone = $data['timezone'];
        $this->is_active = 1;
        $this->save();
    }

    public function updatePassword(array $data): void {
        $operatorId = $data['id'];
        $this->getOperatorById($operatorId);

        if ($this->loaded()) {
            $password = $data['new-password'];
            $this->password = self::hashPassword($password, PASSWORD_DEFAULT);

            $this->save();
        }
    }

    public function updateEmail(array $data): void {
        $operatorId = $data['id'];
        $this->getOperatorById($operatorId);

        if ($this->loaded()) {
            $this->email = $data['email'];

            $this->save();
        }
    }

    public function updateTimeZone(array $data): void {
        $operatorId = $data['id'];
        $this->getOperatorById($operatorId);

        if ($this->loaded()) {
            $this->timezone = $data['timezone'];

            $this->save();
        }
    }

    public function updateNotificationPreferences(
        \Type\UnreviewedItemsReminderFrequencyType $unreviewedItemsReminderFrequency,
    ): void {
        if ($this->loaded()) {
            $this->unreviewed_items_reminder_freq = $unreviewedItemsReminderFrequency->value;

            $this->save();
        }
    }

    public function updateReviewedQueueCnt(array $data): void {
        $operatorId = $data['id'];
        $this->getOperatorById($operatorId);

        if ($this->loaded()) {
            $this->review_queue_cnt = $data['review_queue_cnt'];
            $this->review_queue_updated_at = gmdate('Y-m-d H:i:s');

            $this->save();
        }
    }

    public function updateLastEventTime(array $data): void {
        $operatorId = $data['id'];
        $this->getOperatorById($operatorId);

        if ($this->loaded()) {
            $this->last_event_time = $data['last_event_time'];

            $this->save();
        }
    }

    public function closeAccount(): void {
        if ($this->loaded()) {
            $this->is_closed = 1;

            $this->save();
        }
    }

    public function deleteAccount(): void {
        if ($this->loaded()) {
            $this->erase();
        }
    }

    public function removeData(): void {
        if ($this->loaded()) {
            $params = [
                ':operator_id' => $this->id,
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
    }

    public function activateByOperator(int $operatorId): void {
        $this->getOperatorById($operatorId);

        if ($this->loaded()) {
            $this->is_active = 1;
            $this->save();
        }
    }

    public function getByEmail(string $email): self|null|false {
        return $this->load(
            ['"email"=?', $email],
        );
    }

    public function getActivatedByEmail(string $email): int {
        $isActive = 1;
        $isClosed = 0;

        $filters = ['LOWER(email)=LOWER(?) AND "is_active"=? AND "is_closed"=?', $email, $isActive, $isClosed];
        $this->load($filters);

        return $this->loaded();
    }

    public function getOperatorById(int $id): self|null|false {
        return $this->load(
            ['"id"=? AND "is_closed"=?', $id, 0],
        );
    }

    public function verifyPassword(string $password): bool {
        if (!$this->loaded() || !$this->password) {
            return false;
        }

        $pepper = \Utils\Variables::getPepper();
        $pepperedPassword = \hash_hmac('sha256', $password, $pepper);

        return \password_verify($pepperedPassword, $this->password);
    }

    public function getAll(): array {
        return $this->find(null, ['order' => 'email ASC']);
    }

    public static function hashPassword(string $password): string {
        $pepper = \Utils\Variables::getPepper();
        $pepperedPassword = \hash_hmac('sha256', $password, $pepper);

        return \password_hash($pepperedPassword, PASSWORD_DEFAULT);
    }
}
