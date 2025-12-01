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

namespace Models\Enrichment;

class Device extends \Models\Enrichment\Base {
    protected string $ua;
    protected ?string $device;
    protected ?string $browser_name;
    protected ?string $browser_version;
    protected ?string $os_name;
    protected ?string $os_version;
    protected bool $modified;
    protected bool $checked = true;

    public function __construct() {
    }

    public function init(array $data): void {
        $this->ua               = $data['ua'];
        $this->device           = $data['device'];
        $this->browser_name     = $data['browser_name'];
        $this->browser_version  = $data['browser_version'];
        $this->os_name          = $data['os_name'];
        $this->os_version       = $data['os_version'];
        $this->modified         = $data['modified'];
    }

    public function prepareUpdate(): array {
        $params = $this->queryParams();
        unset($params[':ua']);

        $placeholders = array_keys($params);
        $updateString = $this->updateStringByPlaceholders($placeholders);

        return [$params, $updateString];
    }

    public function updateEntityInDb(int $entityId, int $apiKey): void {
        // total_visit and total_account should remain still
        [$params, $updateString] = $this->prepareUpdate();

        $params['entity_id'] = $entityId;
        $params['key'] = $apiKey;

        $query = ("
            UPDATE event_ua_parsed
            SET {$updateString}
            WHERE
                event_ua_parsed.id = :entity_id AND
                event_ua_parsed.key = :key
        ");

        $model = new \Models\Device();
        $model->execQuery($query, $params);
    }
}
