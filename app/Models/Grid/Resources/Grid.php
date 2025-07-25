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

namespace Models\Grid\Resources;

class Grid extends \Models\Grid\Base\Grid {
    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->query = new Query($apiKey);
    }

    public function getAllResources(): array {
        $data = $this->getGrid();
        if (isset($data['data'])) {
            $data['data'] = $this->extendWithSuspiciousUrl($data['data']);
        }

        return $data;
    }

    private function extendWithSuspiciousUrl(array $result): array {
        if (is_array($result) && count($result)) {
            $suspiciousUrlWords = \Utils\SuspiciousWordsUrl::getWords();
            foreach ($result as &$record) {
                $record['suspicious'] = $this->isUrlSuspicious($suspiciousUrlWords, $record['url']);
            }
            unset($record);
        }

        return $result;
    }

    private function isUrlSuspicious(array $substrings, string $url): bool {
        foreach ($substrings as $sub) {
            if (stripos($url, $sub) !== false) {
                return true;
            }
        }

        return false;
    }
}
