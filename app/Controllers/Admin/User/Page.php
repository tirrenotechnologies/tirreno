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

namespace Controllers\Admin\User;

class Page extends \Controllers\Pages\Base {
    use \Traits\ApiKeys;

    public $page = 'AdminUser';

    public function getPageParams(): array {
        $dataController = new Data();
        $userId = $this->integerParam($this->f3->get('PARAMS.userId'));
        $hasAccess = $dataController->checkIfOperatorHasAccess($userId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        [$scheduledForDeletion, $errorCode] = $dataController->getScheduledForDeletion($userId);
        $user = $dataController->getUserById($userId);

        $pageTitle = $this->getInternalPageTitleWithPostfix($user['page_title']);
        $payload = $dataController->getPayloadColumns($userId);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'admin/user.html',
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'USER'                          => $user,
            'SCHEDULED_FOR_DELETION'        => $scheduledForDeletion,
            'PAGE_TITLE'                    => $pageTitle,
            'PAYLOAD'                       => $payload,
            'JS'                            => 'admin_user.js',
            'ERROR_CODE'                    => $errorCode,
            'OFFSET'                        => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        if ($this->isPostRequest()) {
            $params = $this->f3->get('POST');
            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'];
            // recall user data
            $pageParams['USER'] = $dataController->getUserById($userId);
        }

        [$scheduledForBlacklist, $errorCode] = $dataController->getScheduledForBlacklist($userId);
        if ($scheduledForBlacklist) {
            $this->f3->set('SESSION.extra_message_code', $errorCode ?? \Utils\ErrorCodes::USER_BLACKLISTING_QUEUED);
        }

        return parent::applyPageParams($pageParams);
    }
}
