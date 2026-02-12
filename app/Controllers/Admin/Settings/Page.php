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

namespace Tirreno\Controllers\Admin\Settings;

class Page extends \Tirreno\Controllers\Admin\Base\Page {
    public ?string $page = 'AdminSettings';

    public function getPageParams(): array {
        $dataController = new Data();

        $pageParams = [
            'LOAD_DATATABLE'    => true,
            'LOAD_AUTOCOMPLETE' => true,
            'HTML_FILE'         => 'admin/settings.html',
            'JS'                => 'admin_settings.js',
            'TIMEZONES'         => \Tirreno\Utils\Timezones::timezonesList(),
            'CURRENT_VERSION'   => \Tirreno\Utils\VersionControl::fullVersionString(),
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            //$this->f3->reroute('/account');
        }

        // set shared_operators and api_keys params after processing POST request

        $currentOperator = \Tirreno\Utils\Routes::getCurrentRequestOperator();
        $operatorId = $currentOperator->id;

        $coOwners = $dataController->getSharedApiKeyOperators($operatorId);
        $pageParams['SHARED_OPERATORS'] = $coOwners;

        [$isOwner, $apiKeys] = \Tirreno\Utils\ApiKeys::getOperatorApiKeys($operatorId);

        $pageParams['IS_OWNER'] = $isOwner;
        $pageParams['API_KEYS'] = $apiKeys;

        $pageParams['PROFILE'] = $currentOperator;

        return parent::applyPageParams($pageParams);
    }
}
