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

namespace Controllers\Admin\ISP;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function checkIfOperatorHasAccess(int $ispId): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Isp();

        return $model->checkAccess($ispId, $apiKey);
    }

    public function getFullIspInfoById(int $ispId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Isp();
        $result = $model->getFullIspInfoById($ispId, $apiKey);
        $result['lastseen'] = \Utils\ElapsedDate::short($result['lastseen']);

        return $result;
    }

    private function getNumberOfIpsByIspId(int $ispId): int {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Isp();

        return $model->getIpCountById($ispId, $apiKey);
    }

    public function getIspDetails(int $ispId): array {
        $result = [];
        $data = $this->getFullIspInfoById($ispId);

        if (array_key_exists('asn', $data)) {
            $result = [
                'asn'           => $data['asn'],
                'total_fraud'   => $data['total_fraud'],
                'total_visit'   => $data['total_visit'],
                'total_account' => $data['total_account'],
                'total_ip'      => $this->getNumberOfIpsByIspId($ispId),
            ];
        }

        return $result;
    }
}
