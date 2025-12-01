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

namespace Controllers\Admin\FieldAudits;

class Page extends \Controllers\Admin\Base\Page {
    public $page = 'AdminFieldAudits';

    public function getPageParams(): array {
        $pageParams = [
            'SEARCH_PLACEHOLDER'    => $this->f3->get('AdminFieldAuditTrail_search_placeholder'),
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'admin/fieldAudits.html',
            'JS'                    => 'admin_field_audits.js',
        ];

        return parent::applyPageParams($pageParams);
    }
}
