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

namespace Tirreno\Controllers\Admin\Totals;

class Data extends \Tirreno\Controllers\Admin\Base\Data {
    public function getTimeFrameTotal(array $ids, string $type, string $startDate, string $endDate, int $apiKey): array {
        $processErrorMessage = ['ERROR_CODE' => \Tirreno\Utils\ErrorCodes::TOTALS_INVALID_TYPE];

        if (!in_array($type, ['ip', 'isp', 'domain', 'country', 'resource', 'field', 'userAgent'])) {
            return $processErrorMessage;
        }

        $model = null;

        switch ($type) {
            case 'ip':
                $model = new \Tirreno\Models\Ip();
                break;
            case 'isp':
                $model = new \Tirreno\Models\Isp();
                break;
            case 'domain':
                $model = new \Tirreno\Models\Domain();
                break;
            case 'country':
                $model = new \Tirreno\Models\Country();
                break;
            case 'resource':
                $model = new \Tirreno\Models\Resource();
                break;
            case 'field':
                $model = new \Tirreno\Models\FieldAudit();
                break;
            case 'userAgent':
                $model = new \Tirreno\Models\UserAgent();
                break;
        }

        $totals = $model->getTimeFrameTotal($ids, $startDate, $endDate, $apiKey);

        return [
            'SUCCESS_MESSAGE'   => $this->f3->get('AdminTotals_success_message'),
            'totals'            => $totals,
        ];
    }
}
