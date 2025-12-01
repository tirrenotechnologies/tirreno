<?php

/**
 * tirreno ~ open security analytics
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

namespace Controllers\Admin\ManualCheck;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminManualCheck';

    public function getPageParams(): array {
        $dataController = new Data();

        $pageParams = [
            'LOAD_AUTOCOMPLETE' => true,
            'LOAD_DATATABLE'    => true,
            'HTML_FILE'         => 'admin/manualCheck.html',
            'JS'                => 'admin_manual_check.js',
        ];

        $currentOperator = \Utils\Routes::getCurrentRequestOperator();
        $operatorId = $currentOperator->id;

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();
            $pageParams = array_merge($pageParams, $operationResponse);
        }

        $pageParams['HISTORY'] = $dataController->getSearchHistory($operatorId);

        return parent::applyPageParams($pageParams);
    }

    public static function stylizeKey(string $key): string {
        $overwrites = \Base::instance()->get('AdminManualCheck_key_overwrites');

        if (array_key_exists($key, $overwrites)) {
            return $overwrites[$key];
        }

        if ($key === 'profiles' || $key === 'data_breach') {
            $key = sprintf('no_%s', $key);
        }

        return ucfirst(str_replace('_', ' ', $key));
    }
}
