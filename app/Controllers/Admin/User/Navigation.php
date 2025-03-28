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

namespace Controllers\Admin\User;

class Navigation extends \Controllers\Base {
    use \Traits\ApiKeys;
    use \Traits\Navigation;

    public function showIndexPage(): void {
        $this->redirectIfUnlogged();

        $pageController = new Page();
        $this->response = new \Views\Frontend();
        $this->response->data = $pageController->getPageParams();
    }

    public function manageUser(): array {
        $params = $this->f3->get('POST');
        $accountId = $params['userId'] ?? null;

        $dataController = new Data();
        $errorCode = $dataController->validate($accountId, $params);
        $successCode = false;

        if (!$errorCode) {
            $cmd = $params['type'] ?? null;
            $apiKey = $this->getCurrentOperatorApiKeyId();
            switch ($cmd) {
                case 'add':
                    $dataController->addToWatchlist($accountId, $apiKey);
                    $successCode = \Utils\ErrorCodes::USER_HAS_BEEN_SUCCESSFULLY_ADDED_TO_WATCH_LIST;
                    break;

                case 'remove':
                    $dataController->removeFromWatchlist($accountId, $apiKey);
                    $successCode = \Utils\ErrorCodes::USER_HAS_BEEN_SUCCESSFULLY_REMOVED_FROM_WATCH_LIST;
                    break;

                case 'fraud':
                    $dataController->addToBlacklistQueue($accountId, true, $apiKey);
                    $successCode = \Utils\ErrorCodes::USER_FRAUD_FLAG_HAS_BEEN_SET;
                    break;

                case 'legit':
                    $dataController->addToBlacklistQueue($accountId, false, $apiKey);
                    $successCode = \Utils\ErrorCodes::USER_FRAUD_FLAG_HAS_BEEN_UNSET;
                    break;

                case 'reviewed':
                    $dataController->setReviewedFlag($accountId, true, $apiKey);
                    $successCode = \Utils\ErrorCodes::USER_REVIEWED_FLAG_HAS_BEEN_SET;
                    break;
            }
        }

        return ['success' => $successCode];
    }

    public function getUserScoreDetails(): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $params = $this->f3->get('GET');
        $userId = $params['userId'];

        $dataController = new Data();

        return $dataController->getUserScoreDetails($userId, $apiKey);
    }
}
