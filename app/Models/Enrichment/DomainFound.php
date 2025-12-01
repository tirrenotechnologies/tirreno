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

class DomainFound extends \Models\Enrichment\Base {
    protected string $domain;
    protected bool $blockdomains;
    protected bool $disposable_domains;
    protected bool $free_email_provider;
    protected ?string $creation_date;       // date
    protected ?string $expiration_date;     // date
    protected ?int $return_code;
    protected bool $disabled;
    protected ?string $closest_snapshot;    // date
    protected bool $mx_record;
    protected ?string $ip;                  // ipvanyaddress
    protected ?string $geo_ip;
    protected ?string $geo_html;
    protected ?string $web_server;
    protected ?string $hostname;
    protected ?string $emails;
    protected ?string $phone;
    protected string $discovery_date;       // date
    protected ?int $tranco_rank;
    protected bool $checked = true;

    public function init(array $data): void {
        $this->domain               = $data['domain'];
        $this->blockdomains         = $data['blockdomains'];
        $this->disposable_domains   = $data['disposable_domains'];
        $this->free_email_provider  = $data['free_email_provider'];
        $this->creation_date        = $data['creation_date'];
        $this->expiration_date      = $data['expiration_date'];
        $this->return_code          = $data['return_code'];
        $this->disabled             = $data['disabled'];
        $this->closest_snapshot     = $data['closest_snapshot'];
        $this->mx_record            = $data['mx_record'];
        $this->ip                   = $data['ip'];
        $this->geo_ip               = $data['geo_ip'];
        $this->geo_html             = $data['geo_html'];
        $this->web_server           = $data['web_server'];
        $this->hostname             = $data['hostname'];
        $this->emails               = $data['emails'];
        $this->phone                = $data['phone'];
        $this->discovery_date       = $data['discovery_date'];
        $this->tranco_rank          = $data['tranco_rank'];

        $dates = [$this->creation_date, $this->expiration_date, $this->closest_snapshot, $this->discovery_date];

        if (($this->ip && !$this->validateIP($this->ip)) || !$this->validateDates($dates)) {
            throw new \Exception('Validation failed');
        }
    }

    public function prepareUpdate(): array {
        $params = $this->queryParams();
        unset($params[':domain']);

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
            UPDATE event_domain
            SET {$updateString}
            WHERE
                event_domain.id = :entity_id AND
                event_domain.key = :key
        ");

        $model = new \Models\Domain();
        $model->execQuery($query, $params);
    }
}
