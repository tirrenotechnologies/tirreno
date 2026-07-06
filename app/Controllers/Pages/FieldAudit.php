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

class FieldAudit extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'fieldAudit';

    protected function getPageParams(): array {
        $this->assertCanView();

        $fieldId = tirreno('utils')->conversion->getIntUrlParam('fieldId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($fieldId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $field = $this->controller->getFieldById($fieldId, $this->apiKey);
        $pageTitle = tirreno('utils')->render->getInternalPageTitleWithPostfix(strval($field['field_id']));

        return [
            'LOAD_DATATABLE'                => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'fieldAudit.html',
            'FIELD'                         => $field,
            'PAGE_TITLE'                    => $pageTitle,
            'JS'                            => 'field_audit.js',
            'INTERNAL_PAGE'                 => true,
        ];
    }
}
