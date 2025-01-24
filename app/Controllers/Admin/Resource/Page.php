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

namespace Controllers\Admin\Resource;

class Page extends \Controllers\Pages\Base {
    public $page = 'AdminResource';

    public function getPageParams(): array {
        $dataController = new Data();
        $resourceId = $this->integerParam($this->f3->get('PARAMS.resourceId'));
        $hasAccess = $dataController->checkIfOperatorHasAccess($resourceId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $dataController->updateTotalsByResourceId($resourceId);

        $resource = $dataController->getResourceById($resourceId);

        $pageTitle = $resource['url'];
        if ($resource['title']) {
            $pageTitle .= sprintf(' (%s)', $resource['title']);
        }

        $pageTitle = $this->getInternalPageTitleWithPostfix($pageTitle);

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'admin/resource.html',
            'RESOURCE'                      => $resource,
            'PAGE_TITLE'                    => $pageTitle,
            'JS'                            => 'admin_resource.js',
            'OFFSET'                        => \Utils\TimeZones::getCurrentOperatorOffset(),
        ];

        return parent::applyPageParams($pageParams);
    }
}
