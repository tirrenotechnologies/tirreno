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

namespace Controllers\Admin\Totals;

class Navigation extends \Controllers\Admin\Base\Navigation {
    public function __construct() {
        parent::__construct();

        $this->controller = new Data();
        $this->page = null;
    }

    public function getTimeFrameTotal(): array {
        $ids        = \Utils\Conversion::getArrayRequestParam('ids');
        $type       = \Utils\Conversion::getStringRequestParam('type');
        $startDate  = \Utils\Conversion::getStringRequestParam('startDate');
        $endDate    = \Utils\Conversion::getStringRequestParam('endDate');

        return $this->controller->getTimeFrameTotal($ids, $type, $startDate, $endDate, $this->apiKey);
    }
}
