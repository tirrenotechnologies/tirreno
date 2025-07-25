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

namespace Controllers\Admin\Country;

class Page extends \Controllers\Pages\Base {
    use \Traits\ApiKeys;

    public $page = 'AdminCountry';

    public function getPageParams(): array {
        $dataController = new Data();
        $countryId = $this->integerParam($this->f3->get('PARAMS.countryId'));

        $hasAccess = $dataController->checkIfOperatorHasAccess($countryId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $country = $dataController->getCountryById($countryId);
        $pageTitle = $this->getInternalPageTitleWithPostfix($country['value']);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'admin/country.html',
            'COUNTRY'                       => $country,
            'PAGE_TITLE'                    => $pageTitle,
            'JS'                            => 'admin_country.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
