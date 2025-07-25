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

namespace Controllers\Admin\Totals;

class Data extends \Controllers\Base {
    public function getTimeFrameTotal(array $ids, string $type, string $startDate, string $endDate, int $apiKey): array {
        $processErrorMessage = ['ERROR_CODE' => \Utils\ErrorCodes::TOTALS_INVALID_TYPE];

        if (!in_array($type, ['ip', 'isp', 'domain', 'country', 'resource'])) {
            return $processErrorMessage;
        }

        $model = null;

        switch ($type) {
            case 'ip':
                $model = new \Models\Ip();
                break;
            case 'isp':
                $model = new \Models\Isp();
                break;
            case 'domain':
                $model = new \Models\Domain();
                break;
            case 'country':
                $model = new \Models\Country();
                break;
            case 'resource':
                $model = new \Models\Resource();
                break;
        }

        $totals = $model->getTimeFrameTotal($ids, $startDate, $endDate, $apiKey);

        return [
            'SUCCESS_MESSAGE'   => $this->f3->get('AdminTotals_success_message'),
            'totals'            => $totals,
        ];
    }
}
