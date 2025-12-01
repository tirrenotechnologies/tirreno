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

namespace Controllers\Admin\ISP;

class Data extends \Controllers\Admin\Base\Data {
    public function checkIfOperatorHasAccess(int $ispId, int $apiKey): bool {
        return (new \Models\Isp())->checkAccess($ispId, $apiKey);
    }

    public function getFullIspInfoById(int $ispId, int $apiKey): array {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $model = new \Models\Isp();
        $result = $model->getFullIspInfoById($ispId, $apiKey);
        $result['lastseen'] = \Utils\ElapsedDate::short($result['lastseen']);

        return $result;
    }

    private function getNumberOfIpsByIspId(int $ispId, int $apiKey): int {
        return (new \Models\Isp())->getIpCountById($ispId, $apiKey);
    }

    public function getIspDetails(int $ispId, int $apiKey): array {
        $result = [];
        $data = $this->getFullIspInfoById($ispId, $apiKey);

        if (array_key_exists('asn', $data)) {
            $result = [
                'asn'           => $data['asn'],
                'total_fraud'   => $data['total_fraud'],
                'total_visit'   => $data['total_visit'],
                'total_account' => $data['total_account'],
                'total_ip'      => $this->getNumberOfIpsByIspId($ispId, $apiKey),
            ];
        }

        return $result;
    }
}
