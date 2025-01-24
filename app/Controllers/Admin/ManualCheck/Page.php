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

namespace Controllers\Admin\ManualCheck;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminManualCheck';

    public function getPageParams(): array {
        $dataController = new Data();

        $pageParams = [
            'LOAD_AUTOCOMPLETE' => true,
            'LOAD_DATATABLE'    => true,
            'HTML_FILE'         => 'admin/manualCheck.html',
            'JS'                => 'admin_manual_check.js',
        ];

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $params['operator'] = $operatorId;
            $operationResponse = $dataController->proceedPostRequest($params);
            $pageParams = array_merge($pageParams, $operationResponse);
        }

        $pageParams['HISTORY'] = $dataController->getSearchHistory($operatorId);

        return parent::applyPageParams($pageParams);
    }

    public static function stylizeKey(string $key): string {
        $f3 = \Base::instance();
        $overwrites = $f3->get('AdminManualCheck_key_overwrites');

        if (array_key_exists($key, $overwrites)) {
            return $overwrites[$key];
        }

        if ($key === 'profiles' || $key === 'data_breach') {
            $key = sprintf('no_%s', $key);
        }

        return ucfirst(str_replace('_', ' ', $key));
    }
}
