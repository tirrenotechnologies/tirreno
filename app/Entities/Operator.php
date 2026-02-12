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

namespace Tirreno\Entities;

class Operator {
    public int $id;
    public string $email;
    public ?string $password;
    public ?string $firstname;
    public ?string $lastname;
    //public ?int $isActive;
    //public ?int $isClosed;
    public ?string $activationKey;
    //public ?string $createdAt;
    public string $timezone;
    public ?int $reviewQueueCnt;
    public ?string $reviewQueueUpdatedAt;
    public ?string $lastEventTime;
    public string $reminderFreq;
    //public ?string $lastUnreviewedItemsReminderFreq;
    public ?int $blacklistUsersCnt;

    // TODO: do we need isOwner?
    public function __construct(
        int $id,
        string $email,
        ?string $password,
        ?string $firstname,
        ?string $lastname,
        ?string $activationKey,
        string $timezone,
        ?int $reviewQueueCnt,
        ?string $reviewQueueUpdatedAt,
        ?string $lastEventTime,
        string $reminderFreq,
        ?int $blacklistUsersCnt,
    ) {
        $this->id                   = $id;
        $this->email                = $email;
        $this->password             = $password;
        $this->firstname            = $firstname;
        $this->lastname             = $lastname;
        $this->activationKey        = $activationKey;
        $this->timezone             = $timezone;
        $this->reviewQueueCnt       = $reviewQueueCnt;
        $this->reviewQueueUpdatedAt = $reviewQueueUpdatedAt;
        $this->lastEventTime        = $lastEventTime;
        $this->reminderFreq         = $reminderFreq;
        $this->blacklistUsersCnt    = $blacklistUsersCnt;
    }

    public static function getById(int $operatorId): ?self {
        $model = new \Tirreno\Models\Operator();
        $operator = $model->getOperatorById($operatorId);

        if (!$operator) {
            return null;
        }

        return new self(
            $operator['id'],
            $operator['email'],
            $operator['password'],
            $operator['firstname'],
            $operator['lastname'],
            $operator['activation_key'],
            $operator['timezone'],
            $operator['review_queue_cnt'],
            $operator['review_queue_updated_at'],
            $operator['last_event_time'],
            $operator['unreviewed_items_reminder_freq'],
            $operator['blacklist_users_cnt'],
        );
    }
}
