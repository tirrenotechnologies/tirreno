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

namespace Tirreno\Core\Services;

// should be identical to current operator
class Sysop extends \Tirreno\Entities\Operator {
    public function __construct() {
        // we could return current operator object right now but we cannot
        $operatorId = tirreno('session')->getCurrentOperator()->id;

        $operator = tirreno('models')->operator->getOperatorById($operatorId);

        if (!$operator) {
            $operator = tirreno('models')->operator->getOperatorById(tirreno('utils')->constants->GUEST_OPERATOR_ID);
        }

        $rolesPermissions = tirreno('utils')->operatorAccess->getRolesWithPermissions($operator['id']);

        parent::__construct(
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
}
