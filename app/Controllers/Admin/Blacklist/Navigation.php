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

namespace Controllers\Admin\Blacklist;

class Navigation extends \Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = new Page();
    }

    public function getList(): array {
        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function setBlacklistUsersCount(bool $cache = false): array {
        return $this->apiKey ? $this->controller->setBlacklistUsersCount($cache, $this->apiKey) : [];
    }

    public function removeItemFromList(): array {
        if (!$this->apiKey || !$this->id) {
            return [];
        }

        $type   = \Utils\Conversion::getStringRequestParam('type');
        $this->controller->removeItemFromBlacklist($this->id, $type, $this->apiKey);
        $successCode = \Utils\ErrorCodes::ITEM_REMOVED_FROM_BLACKLIST;

        return [
            'success'   => $successCode,
            'id'        => $this->id,
        ];
    }
}
