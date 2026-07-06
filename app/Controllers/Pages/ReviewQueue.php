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

class ReviewQueue extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'reviewQueue';

    protected function getPageParams(): array {
        $this->assertCanView();

        return [
            'LOAD_UPLOT'            => true,
            'LOAD_DATATABLE'        => true,
            'LOAD_CHOICES'          => true,
            'LOAD_AUTOCOMPLETE'     => true,
            'HTML_FILE'             => 'reviewQueue.html',
            'JS'                    => 'review_queue.js',
            'RULES'                 => tirreno('controllers')->rules->getAllRulesByApiKey($this->apiKey),
            'INTERNAL_PAGE'         => true,
        ];
    }

    public function getList(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getList($this->apiKey) : [];
    }

    public function getChart(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getChart($this->apiKey) : [];
    }

    public function setNotReviewedCount(bool $cache = false): array {
        $this->assertCanEdit();

        return $this->apiKey ? $this->controller->setNotReviewedCount($cache, $this->apiKey) : [];
    }

    public function reviewUser(): array {
        $this->assertCanEdit();

        $accountId  = tirreno('utils')->conversion->getIntRequestParam('userId');
        $cmd        = tirreno('utils')->conversion->getStringRequestParam('type');

        if ($cmd === 'fraud') {
            return ['success' => $this->controller->addToBlacklist($accountId, $this->apiKey)];
        }

        if ($cmd === 'legit') {
            return ['success' => $this->controller->addToWhitelist($accountId, $this->apiKey)];
        }

        return ['success' => false];
    }
}
