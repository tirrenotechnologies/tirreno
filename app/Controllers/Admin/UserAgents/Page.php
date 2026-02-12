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

namespace Tirreno\Controllers\Admin\UserAgents;

class Page extends \Tirreno\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminUserAgents';

    public function getPageParams(): array {
        $searchPlacholder = $this->f3->get('AdminUserAgents_search_placeholder');

        $pageParams = [
            'SEARCH_PLACEHOLDER'            => $searchPlacholder,
            'LOAD_UPLOT'                    => true,
            'LOAD_DATATABLE'                => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/userAgents.html',
            'JS'                            => 'admin_user_agents.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
