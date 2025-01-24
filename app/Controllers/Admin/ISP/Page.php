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

namespace Controllers\Admin\ISP;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminIsp';

    public function getPageParams(): array {
        $dataController = new Data();
        $ispId = $this->integerParam($this->f3->get('PARAMS.ispId'));
        $hasAccess = $dataController->checkIfOperatorHasAccess($ispId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $dataController->updateTotalsByIspId($ispId);

        $isp = $dataController->getFullIspInfoById($ispId);
        $pageTitle = $this->getInternalPageTitleWithPostfix(strval($isp['asn']));

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/isp.html',
            'ISP'                           => $isp,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'JS'                            => 'admin_isp.js',
            'OFFSET'                        => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        return parent::applyPageParams($pageParams);
    }
}
