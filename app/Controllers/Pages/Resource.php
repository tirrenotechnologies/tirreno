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

class Resource extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'resource';

    protected function getPageParams(): array {
        $this->assertCanView();

        $resourceId = tirreno('utils')->conversion->getIntUrlParam('resourceId');
        $hasAccess = tirreno('controllers')->resource->checkIfOperatorHasAccess($resourceId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $resource = tirreno('controllers')->resource->getResourceById($resourceId, $this->apiKey);

        $pageTitle = $resource['url'];
        if ($resource['title']) {
            $pageTitle .= sprintf(' (%s)', $resource['title']);
        }

        $pageTitle = tirreno('utils')->render->getInternalPageTitleWithPostfix($pageTitle);

        return [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'HTML_FILE'                     => 'resource.html',
            'RESOURCE'                      => $resource,
            'PAGE_TITLE'                    => $pageTitle,
            'JS'                            => 'resource.js',
            'INTERNAL_PAGE'                 => true,
        ];
    }
}
