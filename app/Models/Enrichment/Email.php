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

class Email extends \Models\Enrichment\Base {
    protected string $email;
    protected bool $blockemails;
    protected bool $data_breach;
    protected int $data_breaches;
    protected ?string $earliest_breach;
    protected int $profiles;
    protected bool $domain_contact_email;
    protected string $domain;
    protected ?bool $alert_list;
    protected bool $checked = true;

    public function __construct() {
    }

    public function init(array $data): void {
        $this->email                = $data['email'];
        $this->blockemails          = $data['blockemails'];
        $this->data_breach          = $data['data_breach'];
        $this->data_breaches        = $data['data_breaches'];
        $this->earliest_breach      = $data['earliest_breach'];
        $this->profiles             = $data['profiles'];
        $this->domain_contact_email = $data['domain_contact_email'];
        $this->domain               = $data['domain'];
        $this->alert_list           = $data['alert_list'];

        if (!$this->validateDates([$this->earliest_breach])) {
            throw new \Exception('Validation failed');
        }
    }

    public function prepareUpdate(): array {
        $params = $this->queryParams();
        unset($params[':email']);
        // !
        unset($params[':domain']);

        // if new alert_list is null -- don't override
        if ($params[':alert_list'] === null) {
            unset($params[':alert_list']);
        }

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
            UPDATE event_email
            SET {$updateString}
            WHERE
                event_email.id = :entity_id AND
                event_email.key = :key
        ");

        $model = new \Models\Device();
        $model->execQuery($query, $params);
    }
}
