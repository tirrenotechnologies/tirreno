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

namespace Controllers\Admin\Bot;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminBot';

    public function getPageParams(): array {
        $dataController = new Data();
        $botId = $this->integerParam($this->f3->get('PARAMS.botId'));
        $hasAccess = $dataController->checkIfOperatorHasAccess($botId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $bot = $dataController->getBotDetails($botId);
        $pageTitle = $this->getInternalPageTitleWithPostfix($bot['id']);
        $isEnrichable = $dataController->isEnrichable();

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
            $params = $this->f3->get('POST');
            $operationResponse = $dataController->proceedPostRequest($params);

            $pageParams = array_merge($pageParams, $operationResponse);
            $pageParams['CMD'] = $params['cmd'];
            // recall bot data
            $pageParams['BOT'] = $dataController->getBotDetails($botId);
        }

        return parent::applyPageParams($pageParams);
    }
}
