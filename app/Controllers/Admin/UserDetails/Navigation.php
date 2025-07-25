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

namespace Controllers\Admin\UserDetails;

class Navigation extends \Controllers\Base {
    use \Traits\ApiKeys;
    use \Traits\Navigation;

    public function getUserDetails(): array {
        $dataController = new Data();
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $userId = $this->f3->get('GET.userId');
        $hasAccess = $dataController->checkIfOperatorHasAccess($userId, $apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        return $dataController->getUserDetails($userId, $apiKey);
    }

    public function getUserEnrichmentDetails(): array {
        $dataController = new Data();
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $userId = $this->f3->get('GET.userId');
        $hasAccess = $dataController->checkIfOperatorHasAccess($userId, $apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        return $dataController->getUserEnrichmentDetails($userId, $apiKey);
    }
}
