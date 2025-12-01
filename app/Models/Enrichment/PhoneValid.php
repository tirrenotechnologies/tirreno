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

class PhoneValid extends \Models\Enrichment\Base {
    protected string $phone_number;
    protected int $profiles;
    protected ?string $iso_country_code;
    protected int $calling_country_code;
    protected string $national_format;
    protected bool $invalid;
    protected ?string $validation_errors;
    protected ?string $carrier_name;
    protected string $type;
    protected ?bool $alert_list;
    protected bool $checked = true;
    protected int $country_code;

    public function __construct() {
    }

    public function init(array $data): void {
        $this->phone_number = $data['phone_number'];
        $this->profiles = $data['profiles'];
        $this->iso_country_code = $data['iso_country_code'];
        $this->calling_country_code = $data['calling_country_code'];
        $this->national_format = $data['national_format'];
        $this->invalid = $data['invalid'];
        $this->validation_errors = $data['validation_error'];
        $this->carrier_name = $data['carrier_name'];
        $this->type = $data['type'];
        $this->alert_list = $data['alert_list'];

        if ($this->invalid || $this->validation_errors !== null) {
            throw new \Exception('Validation failed');
        }
    }

    public function prepareUpdate(): array {
        $params = $this->queryParams();
        unset($params[':phone_number']);

        $params[':validation_errors'] = json_encode($params[':validation_errors']);

        // if new alert_list is null -- don't override
        if ($params[':alert_list'] === null) {
            unset($params[':alert_list']);
        }

        $placeholders = array_keys($params);
        $updateString = $this->updateStringByPlaceholders($placeholders);

        return [$params, $updateString];
    }

    public function updateEntityInDb(int $entityId, int $apiKey): void {
        $this->country_code = 0;

        if ($this->iso_country_code !== null) {
            $countryModel = new \Models\Country();
            $this->country_code = $countryModel->getCountryIdByIso($this->iso_country_code);
        }

        [$params, $updateString] = $this->prepareUpdate();

        $params['entity_id'] = $entityId;
        $params['key'] = $apiKey;

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
