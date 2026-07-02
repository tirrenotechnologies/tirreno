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
    // TODO: should be private and immutable, should provide getters for operatorFields
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
    public array $roles;            // [string]
    public array $permissions;      // [int]

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
        array $rolesPermissions,
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
        $this->roles                = array_keys($rolesPermissions);
        $this->permissions          = array_unique(array_column(array_merge(...array_values($rolesPermissions)), 'permission_value'));
    }

    public static function getById(int $operatorId): self {
        $operator = tirreno('models')->operator->getOperatorById($operatorId);

        if (!$operator) {
            $operator = tirreno('models')->operator->getOperatorById(tirreno('utils')->constants->GUEST_OPERATOR_ID);
        }

        $rolesPermissions = tirreno('utils')->operatorAccess->getRolesWithPermissions($operator['id']);

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
            $rolesPermissions
        );
    }

    public function addRole(string $role): void {
        tirreno('utils')->operatorAccess->addOperatorRole($role, $this->id);
    }

    public function hasRole(string $role): bool {
        return tirreno('utils')->operatorAccess->operatorHasRole($role, $this->id);
    }

    public function removeRole(string $role): void {
        tirreno('utils')->operatorAccess->removeOperatorRole($role, $this->id);
    }

    public function getRoles(): array {
        return tirreno('utils')->operatorAccess->getRoles($this->id);
    }

    public function hasPermission(int $permission): bool {
        return tirreno('utils')->operatorAccess->hasPermission($permission, $this->id);
    }

    public function isSuperuser(): bool {
        return in_array('superuser', $this->roles);
    }

    public function isGuest(): bool {
        return $this->id === tirreno('utils')->constants->GUEST_OPERATOR_ID;
    }

    public function isLoggedIn(): bool {
        return !$this->isGuest();
    }

    public function viewable(string $pageValue): bool {
        return tirreno('utils')->operatorAccess->viewable($pageValue, $this->id);
    }

    public function editable(string $pageValue): bool {
        return tirreno('utils')->operatorAccess->editable($pageValue, $this->id);
    }

    public function deleteable(string $pageValue): bool {
        return tirreno('utils')->operatorAccess->deleteable($pageValue, $this->id);
    }

    public function publishable(string $pageValue): bool {
        return tirreno('utils')->operatorAccess->publishable($pageValue, $this->id);
    }

    public function adminable(string $pageValue): bool {
        return tirreno('utils')->operatorAccess->adminable($pageValue, $this->id);
    }
}
