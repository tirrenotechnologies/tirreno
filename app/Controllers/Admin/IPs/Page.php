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

namespace Controllers\Admin\IPs;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminIps';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminIps_search_placeholder');

        $pageParams = [
            'SEARCH_PLACEHOLDER'    => $searchPlacholder,
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'admin/ips.html',
            'JS'                    => 'admin_ips.js',
            'OFFSET'                => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        return parent::applyPageParams($pageParams);
    }
}
