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

namespace Controllers\Admin\Logbook;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminLogbook';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminLogbook_search_placeholder');

        $pageParams = [
            'SEARCH_PLACEHOLDER' => $searchPlacholder,
            'LOAD_ACCEPT_LANGUAGE_PARSER' => true,
            'LOAD_DATATABLE' => true,
            'LOAD_AUTOCOMPLETE' => true,
            'HTML_FILE' => 'admin/logbook.html',
            'JS' => 'admin_logbook.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
