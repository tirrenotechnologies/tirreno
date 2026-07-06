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

class Watchlist extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'watchlist';

    protected function getPageParams(): array {
        $this->assertCanView();

        $users = tirreno('controllers')->watchlist->getImportantUsers($this->apiKey);
        $searchPlacholder = tirreno('storage')->get('users_search_placeholder');

        return [
            'SEARCH_PLACEHOLDER'            => $searchPlacholder,
            'IMPORTANT_USERS'               => $users,
            'LOAD_DATATABLE'                => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'watchlist.html',
            'JS'                            => 'watchlist.js',
            'INTERNAL_PAGE'                 => true,
        ];
    }

    public function removeUserFromList(): array {
        $this->assertCanDelete();

        $userId = tirreno('utils')->conversion->getIntRequestParam('userId');

        $this->controller->removeFromWatchlist($userId, $this->apiKey);
        $successCode = tirreno('utils')->errorCodes->USER_REMOVED_FROM_WATCHLIST;

        return [
            'success' => $successCode,
            'userId' => $userId,
        ];
    }
}
