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

namespace Tirreno\Models\Grid\Resources;

class Grid extends \Tirreno\Models\Grid\Base\Grid {
    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->queryModel = new Query($apiKey);
    }

    public function getResourcesByUserId(int $userId): array {
        $params = [':account_id' => $userId];

        $data = $this->getGrid($this->idsModel->getResourcesIdsByUserId(), $params);
        if (isset($data['data'])) {
            $data['data'] = $this->extendWithSuspiciousUrl($data['data']);
        }

        return $data;
    }

    public function getAll(): array {
        $data = $this->getGrid();
        if (isset($data['data'])) {
            $data['data'] = $this->extendWithSuspiciousUrl($data['data']);
        }

        return $data;
    }

    private function extendWithSuspiciousUrl(array $result): array {
        if (count($result)) {
            $suspiciousUrlList = \Tirreno\Utils\Assets\Lists\Url::getList();
            foreach ($result as &$record) {
                $record['suspicious'] = $this->isUrlSuspicious($suspiciousUrlList, $record['url']);
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
