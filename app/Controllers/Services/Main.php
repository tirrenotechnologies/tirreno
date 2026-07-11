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

namespace Tirreno\Controllers\Services;

class Main extends \Tirreno\Controllers\Services\Base {
    public function getCurrentTime(\Tirreno\Entities\Operator $operator): array {
        $offset = tirreno('utils')->timezones->getOperatorOffset($operator);
        $now = time() + $offset;
        $day = tirreno('utils')->constants->SECONDS_IN_DAY;
        $firstJan = mktime(0, 0, 0, 1, 1, intval(gmdate('Y')));

        $day = tirreno('utils')->conversion->intVal(ceil(($now - $firstJan) / $day), 0);

        return [
            'clock_offset'      => $offset,
            'clock_day'         => ($day < 10 ? '00' : ($day < 100 ? '0' : '')) . strval($day),
            'clock_time_his'    => date('H:i:s', $now),
            'clock_timezone'    => 'UTC' . (($offset < 0) ? '-' . date('H:i', -$offset) : '+' . date('H:i', $offset)),
        ];
    }

    public function getConstants(): array {
        $constants = tirreno('assets')->uiConstants->getConstantsObj();
        $constants = $constants::listConstants();

        return $constants ? $constants : [];
    }

    public function getSearchResults(?string $query, int $apiKey): array {
        $result = [];

        if ($query === '' || $query === null) {
            return ['suggestions' => $result];
        }

        $model = new \Tirreno\Models\Search\Domain();
        $result1 = $model->searchByDomain($query, $apiKey);

        $model = new \Tirreno\Models\Search\Ip();
        $result2 = $model->searchByIp($query, $apiKey);

        $model = new \Tirreno\Models\Search\Isp();
        $result3 = $model->searchByIsp($query, $apiKey);

        $model = new \Tirreno\Models\Search\User();
        $result4 = $model->searchByUserId($query, $apiKey);

        $model = new \Tirreno\Models\Search\Email();
        $result5 = $model->searchByEmail($query, $apiKey);

        $model = new \Tirreno\Models\Search\Phone();
        $result6 = $model->searchByPhone($query, $apiKey);

        $result = array_merge($result1, $result2, $result3, $result4, $result5, $result6);
        $iters = count($result);

        for ($i = 0; $i < $iters; ++$i) {
            $result[$i]['data'] = [
                'category'       => $result[$i]['groupName'],
                'id'             => $result[$i]['id'],
                'entityId'       => $result[$i]['entityId'],
                'score'          => $result[$i]['score'] ?? null,
                'fraud'          => $result[$i]['fraud'] ?? null,
                'added_to_review'=> $result[$i]['added_to_review'] ?? null,
                'country_iso'    => $result[$i]['country_iso'] ?? null,
            ];
        }

        return [
            'suggestions' => $result,
        ];
    }
}
