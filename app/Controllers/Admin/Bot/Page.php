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

namespace Controllers\Admin\Bot;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminBot';

    public function getPageParams(): array {
        $dataController = new Data();
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $botId = \Utils\Conversion::getIntUrlParam('botId');
        $hasAccess = $dataController->checkIfOperatorHasAccess($botId, $apiKey);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $bot = $dataController->getBotDetails($botId, $apiKey);
        $pageTitle = $this->getInternalPageTitleWithPostfix(strval($bot['id']));
        $isEnrichable = $dataController->isEnrichable($apiKey);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/bot.html',
            'BOT'                           => $bot,
            'PAGE_TITLE'                    => $pageTitle,
            'LOAD_UPLOT'                    => true,
            'JS'                            => 'admin_bot.js',
            'IS_ENRICHABLE'                 => $isEnrichable,
        ];

        if ($this->isPostRequest()) {
            $operationResponse = $dataController->proceedPostRequest();

            $pageParams = array_merge($pageParams, $operationResponse);
            // recall bot data
            $pageParams['BOT'] = $dataController->getBotDetails($botId, $apiKey);
        }

        return parent::applyPageParams($pageParams);
    }
}
