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

class Country extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'country';

    protected function getPageParams(): array {
        $this->assertCanView();

        $countryId = tirreno('utils')->conversion->getIntUrlParam('countryId');
        $hasAccess = tirreno('controllers')->country->checkIfOperatorHasAccess($countryId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $country = tirreno('controllers')->country->getCountryById($countryId, $this->apiKey);
        $pageTitle = tirreno('utils')->render->getInternalPageTitleWithPostfix($country['name']);

        return [
            'LOAD_DATATABLE'                => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'country.html',
            'COUNTRY'                       => $country,
            'PAGE_TITLE'                    => $pageTitle,
            'JS'                            => 'country.js',
            'INTERNAL_PAGE'                 => true,
        ];
    }
}
