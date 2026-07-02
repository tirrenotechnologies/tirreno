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

class Isp extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'isp';

    protected function getPageParams(): array {
        $this->assertCanView();

        $ispId = tirreno('utils')->conversion->getIntUrlParam('ispId');
        $hasAccess = tirreno('controllers')->isp->checkIfOperatorHasAccess($ispId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $isp = tirreno('controllers')->isp->getFullIspInfoById($ispId, $this->apiKey);
        $pageTitle = tirreno('utils')->render->getInternalPageTitleWithPostfix(strval($isp['asn']));

        return [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'isp.html',
            'ISP'                           => $isp,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'JS'                            => 'isp.js',
            'INTERNAL_PAGE'                 => true,
        ];
    }

    public function getIspDetails(): array {
        $this->assertCanView();

        $ispId = tirreno('utils')->conversion->getIntRequestParam('ispId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($ispId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        return $this->controller->getIspDetails($ispId, $this->apiKey);
    }
}
