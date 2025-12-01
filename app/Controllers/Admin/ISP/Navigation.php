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

namespace Controllers\Admin\ISP;

class Navigation extends \Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = new Page();
    }

    public function getIspDetails(): array {
        $ispId = \Utils\Conversion::getIntRequestParam('ispId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($ispId, $this->apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        return $this->controller->getIspDetails($ispId, $this->apiKey);
    }
}
