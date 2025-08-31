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

namespace Controllers\Admin\Watchlist;

class Navigation extends \Controllers\Base {
    use \Traits\ApiKeys;
    use \Traits\Navigation;

    public function showIndexPage(): void {
        $this->redirectIfUnlogged();

        $pageController = new Page();
        $this->response = new \Views\Frontend();
        $this->response->data = $pageController->getPageParams();
    }

    public function removeUserFromList(): array {
        $dataController = new Data();

        $apiKey = $this->getCurrentOperatorApiKeyId();
        $userId = $this->f3->get('REQUEST.userId');

        $dataController->removeFromWatchlist($userId, $apiKey);
        $successCode = \Utils\ErrorCodes::USER_HAS_BEEN_SUCCESSFULLY_REMOVED_FROM_WATCH_LIST;

        return [
            'success' => $successCode,
            'userId' => $userId,
        ];
    }
}
