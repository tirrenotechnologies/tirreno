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

namespace Models\Grid\Payloads;

class Grid extends \Models\Grid\Base\Grid {
    public function __construct(int $apiKey) {
        parent::__construct();

        $this->apiKey = $apiKey;
        $this->idsModel = new Ids($apiKey);
        $this->query = new Query($apiKey);
    }

    public function getPayloadConfiguration(int $userId): array {
        $this->f3->set('REQUEST.start', 0);
        $this->f3->set('REQUEST.length', 1);
        $this->f3->set('REQUEST.userId', $userId);

        [$query, $params] = $this->query->getData();
        $records = $this->execQuery($query, $params);

        $payload = [];
        if (count($records)) {
            $record = $records[0];
            $payload = $record['payload'];

            $payload = json_decode($payload, true);
            $payload = array_merge(['time' => $record['time']], $payload);
        }

        return $payload;
    }

    public function getPayloadsByUserId(int $userId): array {
        //TODO: $userId inside that model
        $data = $this->getGrid([$userId]);
        $records = $data['data'];
        $iters = count($records);
        $payloads = [];

        for ($i = 0; $i < $iters; ++$i) {
            $record = $records[$i];

            $payload = $record['payload'];
            $payloads[] = $payload;

            $payload = json_decode($payload, true);
            $r = $payload;
            $r['time'] = $record['time'];

            $records[$i] = $r;
        }

        $data['data'] = $records;

        $payloads = array_unique($payloads);
        if (count($payloads) > 1) {
            $data['data'] = [];
            $data['recordsTotal'] = 0;
            $data['recordsFiltered'] = 0;
        }

        return $data;
    }
}
