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

class Resources extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'resources';

    protected function getPageParams(): array {
        $this->assertCanView();

        return [
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_CHOICES'          => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'resources.html',
            'JS'                    => 'resources.js',
            'FILE_TYPES'            => tirreno('assets')->fileExtensionsList->getKeys(),
            'INTERNAL_PAGE'         => true,
        ];
    }

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function getChart(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getChart($this->apiKey) : [];
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
