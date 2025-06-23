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

namespace Controllers\Admin\Blacklist;

class Navigation extends \Controllers\Base {
    use \Traits\ApiKeys;
    use \Traits\Navigation;

    public function showIndexPage(): void {
        $this->redirectIfUnlogged();

        $pageController = new Page();
        $this->response = new \Views\Frontend();
        $this->response->data = $pageController->getPageParams();
    }

    public function getList(): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        return $apiKey ? (new Data())->getList($apiKey) : [];
    }

    public function removeItemFromList(): array {
        $dataController = new Data();

        $apiKey = $this->getCurrentOperatorApiKeyId();
        $id = $this->f3->get('REQUEST.id');
        $type = $this->f3->get('REQUEST.type');

        $dataController->removeItemFromBlacklist($id, $type, $apiKey);
        $successCode = \Utils\ErrorCodes::ITEM_HAS_BEEN_SUCCESSFULLY_REMOVED_FROM_BLACK_LIST;

        return [
            'success' => $successCode,
            'id' => $id,
        ];
    }
}
