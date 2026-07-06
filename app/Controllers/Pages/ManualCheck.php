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

class ManualCheck extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'manualCheck';

    protected function proceedPostRequest(int $apiKey): array {
        $this->assertCanEdit();

        return tirreno('controllers')->manualCheck->performSearch($apiKey);
    }

    protected function getPageParams(): array {
        $this->assertCanView();

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest($this->apiKey) : [];

        $pageParams = [
            'LOAD_AUTOCOMPLETE' => true,
            'LOAD_DATATABLE'    => true,
            'HTML_FILE'         => 'manualCheck.html',
            'JS'                => 'manual_check.js',
            'HISTORY'           => tirreno('controllers')->manualCheck->getSearchHistory($this->operator->id),
            'INTERNAL_PAGE'     => true,
        ];

        return array_merge($pageParams, $postParams);
    }

    public static function stylizeKey(string $key): string {
        $overwrites = tirreno('storage')->get('manualCheck_key_overwrites');

        if (array_key_exists($key, $overwrites)) {
            return $overwrites[$key];
        }

        if ($key === 'profiles' || $key === 'data_breach') {
            $key = sprintf('no_%s', $key);
        }

        return ucfirst(str_replace('_', ' ', $key));
    }
}
