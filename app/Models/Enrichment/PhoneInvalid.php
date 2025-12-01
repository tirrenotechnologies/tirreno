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

class PhoneInvalid extends \Models\Enrichment\Base {
    protected string $phone_number;
    protected bool $invalid;
    protected string $validation_errors;
    //protected ?bool $alert_list = null;
    protected bool $checked = true;
    protected int $country_code;

    public function __construct() {
    }

    public function init(array $data): void {
        $this->phone_number         = $data['phone_number'];
        $this->invalid              = $data['invalid'];
        $this->validation_errors    = $data['validation_error'];
        $this->country_code         = 0;

        if (!$this->invalid) {
            throw new \Exception('Validation failed');
        }
    }

    public function prepareUpdate(): array {
        $params = $this->queryParams();
        unset($params[':phone_number']);

        $params[':validation_errors'] = json_encode($params[':validation_errors']);

        $placeholders = array_keys($params);
        $updateString = $this->updateStringByPlaceholders($placeholders);

        return [$params, $updateString];
    }

    public function updateEntityInDb(int $entityId, int $apiKey): void {
        [$params, $updateString] = $this->prepareUpdate();

        $params['entity_id'] = $entityId;
        $params['key'] = $apiKey;

        // other params will stay still
        $query = ("
            UPDATE event_phone
            SET {$updateString}
            WHERE
                event_phone.id = :entity_id AND
                event_phone.key = :key
        ");

        $model = new \Models\Phone();
        $model->execQuery($query, $params);
    }
}
