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

namespace Tirreno\Controllers\Pages;

class Countries extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'countries';

    protected function getPageParams(): array {
        $this->assertCanView();

        return [
            'LOAD_JVECTORMAP'       => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'countries.html',
            'JS'                    => 'countries.js',
            'INTERNAL_PAGE'         => true,
        ];
    }

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function getMap(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getMap($this->apiKey) : [];
    }

    public function getTimeFrameTotal(): array {
        $this->assertCanView();

        if (!$this->apiKey) {
            return [];
        }

        $ids        = tirreno('utils')->conversion->getArrayRequestParam('ids');
        $startDate  = tirreno('utils')->conversion->getStringRequestParam('startDate');
        $endDate    = tirreno('utils')->conversion->getStringRequestParam('endDate');

        return $this->controller->getTimeFrameTotal($ids, $startDate, $endDate, $this->apiKey);
    }
}
