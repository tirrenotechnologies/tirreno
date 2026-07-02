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

class Settings extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'settings';

    protected function proceedPostRequest(): array {
        $this->assertCanEdit();

        $cmd = tirreno('utils')->conversion->getStringRequestParam('cmd');

        if ($cmd === 'closeAccount') {
            $this->assertCanDelete();
        }

        return match ($cmd) {
            'changeEmail'                   => tirreno('controllers')->settings->changeEmail(),
            'changeTimezone'                => tirreno('controllers')->settings->changeTimezone(),
            'changePassword'                => tirreno('controllers')->settings->changePassword(),
            'closeAccount'                  => tirreno('controllers')->settings->closeAccount(),
            'updateNotificationPreferences' => tirreno('controllers')->settings->updateNotificationPreferences(),
            'changeRetentionPolicy'         => tirreno('controllers')->settings->changeRetentionPolicy(),
            'inviteCoOwner'                 => tirreno('controllers')->settings->inviteCoOwner(),
            'removeCoOwner'                 => tirreno('controllers')->settings->removeCoOwner(),
            'checkUpdates'                  => tirreno('controllers')->settings->checkUpdates(),
            default => []
        };
    }

    protected function getPageParams(): array {
        $this->assertCanView();

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest() : [];

        $currentOperator = tirreno('utils')->routes->getCurrentRequestOperator();
        $operatorId = $currentOperator->id;
        [$isOwner, $apiKeys] = tirreno('utils')->apiKeys->getOperatorApiKeys($operatorId);

        $pageParams = [
            'LOAD_DATATABLE'    => true,
            'LOAD_AUTOCOMPLETE' => true,
            'HTML_FILE'         => 'settings.html',
            'JS'                => 'settings.js',
            'TIMEZONES'         => tirreno('utils')->timezones->timezonesList(),
            'CURRENT_VERSION'   => tirreno('utils')->versionControl->fullVersionString(),
            'SHARED_OPERATORS'  => tirreno('controllers')->settings->getSharedApiKeyOperators($operatorId),
            'IS_OWNER'          => $isOwner,
            'API_KEYS'          => $apiKeys,
            'PROFILE'           => $currentOperator,
            'INTERNAL_PAGE'     => true,
        ];

        return array_merge($pageParams, $postParams);
    }
}
