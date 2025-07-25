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

namespace Controllers\Admin\Blacklist;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminBlacklist';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminBlacklist_search_placeholder');

        $pageParams = [
            'SEARCH_PLACEHOLDER'    => $searchPlacholder,
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_CHOICES'          => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'admin/blacklist.html',
            'JS'                    => 'admin_blacklist.js',
            'ENTITY_TYPES'          => \Utils\Constants::get('ENTITY_TYPES'),
        ];

        return parent::applyPageParams($pageParams);
    }
}
