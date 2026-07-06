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

class Emails extends \Tirreno\Controllers\Pages\Base {
    protected string $page = 'emails';

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function getEmailDetails(): array {
        $this->assertCanView();

        return $this->apiKey && $this->id ? $this->controller->getEmailDetails($this->id, $this->apiKey) : [];
    }

    public function enrichEntity(): array {
        $this->assertCanEdit();

        if (!$this->apiKey) {
            return [];
        }

        $enrichmentKey  = tirreno('utils')->apiKeys->getCurrentOperatorEnrichmentKeyString();
        $entityId       = tirreno('utils')->conversion->getIntRequestParam('entityId', true);

        return $this->controller->enrichEntity($entityId, $enrichmentKey, $this->apiKey);
    }
}
