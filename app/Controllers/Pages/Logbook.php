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

class Logbook extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'logbook';

    protected function getPageParams(): array {
        $this->assertCanView();

        return [
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'logbook.html',
            'JS'                    => 'logbook.js',
            'INTERNAL_PAGE'         => true,
        ];
    }

    // TODO: daterange ~= event_logbook.started?
    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function getChart(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getChart($this->apiKey) : [];
    }

    public function getLogbookDetails(): array {
        $this->assertCanView();

        return $this->apiKey && $this->id ? $this->controller->getLogbookDetails($this->id, $this->apiKey) : [];
    }
}
