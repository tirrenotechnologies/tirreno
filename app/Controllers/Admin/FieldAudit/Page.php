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

namespace Controllers\Admin\FieldAudit;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminFieldAudit';

    public function getPageParams(): array {
        $dataController = new Data();
        $fieldId = \Utils\Conversion::getIntUrlParam('fieldId');

        $hasAccess = $dataController->checkIfOperatorHasAccess($fieldId);

        if (!$hasAccess) {
            $this->f3->error(404);
        }

        $field = $dataController->getFieldById($fieldId);
        $pageTitle = $this->getInternalPageTitleWithPostfix(strval($field['field_id']));

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'admin/fieldAudit.html',
            'FIELD'                         => $field,
            'PAGE_TITLE'                    => $pageTitle,
            'JS'                            => 'admin_field_audit.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
