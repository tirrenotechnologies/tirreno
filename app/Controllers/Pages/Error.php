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

class Error extends \Tirreno\Controllers\Pages\Base {
    protected string $page = 'error';
    protected bool $allowGuest = true;

    protected function isAllowed(): bool {
        return true;
    }

    public function getPageParams(?array $errorData = null): array {
        $this->assertCanView();

        $pageTitle = tirreno('utils')->render->getInternalPageTitleWithPostfix(strval($errorData['code']));

        return [
            'USE_TEMPLATING_SUBDIR' => true,
            'HTML_FILE'             => 'error.html',
            'ERROR_DATA'            => $errorData ?? [],
            'PAGE_TITLE'            => $pageTitle,
            'INTERNAL_PAGE'         => false,
        ];
    }
}
