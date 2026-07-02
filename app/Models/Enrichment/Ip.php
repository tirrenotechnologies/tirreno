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

namespace Tirreno\Models\Enrichment;

class Ip extends \Tirreno\Models\Enrichment\Base {
    protected string $ip;           // ipvanyaddress
    protected string $country;
    protected ?int $asn;
    protected ?string $name;
    protected bool $hosting;
    protected bool $vpn;
    protected bool $tor;
    protected bool $relay;
    protected bool $starlink;
    protected ?string $description;
    protected bool $blocklist;
    protected array $domains_count;  // list[str]
    protected string $cidr;         // ipvanynetwork
    protected ?bool $alert_list;
    protected bool $checked = true;
    protected ?int $isp;

    public function __construct() {
        // empty
    }

    public function init(array $data): void {
        $this->ip               = $data['ip'];
        $this->country          = $data['country'];
        $this->asn              = $data['asn'];
        $this->name             = $data['name'];
        $this->hosting          = $data['hosting'];
        $this->vpn              = $data['vpn'];
        $this->tor              = $data['tor'];
        $this->relay            = $data['relay'];
        $this->starlink         = $data['starlink'];
        $this->description      = $data['description'];
        $this->blocklist        = $data['blocklist'];
        $this->domains_count    = $data['domains_count'];
        $this->cidr             = $data['cidr'];
        $this->alert_list       = $data['alert_list'];

        if (!tirreno('utils')->conversion->filterIp($this->ip) || !$this->validateCIDR($this->cidr)) {
            throw new \Exception('Validation failed');
        }
    }

    public function prepareUpdate(): array {
        $params = $this->queryParams();
        unset($params[':ip']);

        $params[':domains_count'] = json_encode($params[':domains_count']);
        $params[':data_center'] = $params[':hosting'];
        unset($params[':hosting']);

        // if new alert_list is null -- don't override
        if ($params[':alert_list'] === null) {
            unset($params[':alert_list']);
        }

        // set $params[':isp'] later

        unset($params[':asn']);
        unset($params[':name']);
        unset($params[':description']);
        $placeholders = array_keys($params);
        $updateString = $this->updateStringByPlaceholders($placeholders);

        return [$params, $updateString];
    }

    // TODO: update countries table counters
    public function updateEntityInDb(int $entityId, int $apiKey): void {
        $previousIpData = tirreno('models')->ip->getIpById($entityId, $apiKey);
        $previousIspId = count($previousIpData) ? $previousIpData['ispid'] : null;
        $previousCountryId = count($previousIpData) ? $previousIpData['country_id'] : 0;
        // get current isp id
        $this->name = $this->asn !== null ? $this->name : 'N/A';
        $this->asn = $this->asn !== null ? $this->asn : 64496;
        $newIspId = tirreno('models')->isp->getIdByAsn($this->asn, $apiKey);

        $newIspData = [
            'asn'           => $this->asn,
            'name'          => $this->name,
            'description'   => $this->description,
        ];
        $newIspModel = new \Tirreno\Models\Enrichment\Isp();
        $newIspModel->init($newIspData);

        // new isp is not in db
        if ($newIspId === null) {
            $newIspData['lastseen'] = $previousIpData['lastseen'];
            $newIspData['created']  = $previousIpData['created'];
            $newIspId = tirreno('models')->isp->insertRecord($newIspData, $apiKey);
        } else {
            $newIspModel->updateEntityInDb($newIspId, $apiKey);
        }

        $this->isp = $newIspId;

        $newCountryId = tirreno('models')->country->getCountryIdByIso($this->country);

        $countryRecord = tirreno('controllers')->country->getCountryById($newCountryId, $apiKey);
        if (!count($countryRecord)) {
            $newCountryData = [
                'id'        => $newCountryId,
                'lastseen'  => $previousIpData['lastseen'],
                'created'   => $previousIpData['created'],
            ];
            tirreno('models')->country->insertRecord($newCountryData, $apiKey);
        }

        // total_visit and total_account should remain still
        [$params, $updateString] = $this->prepareUpdate();

        $params[':country']     = $newCountryId;
        $params[':entity_id']   = $entityId;
        $params[':key']         = $apiKey;

        $query = ("
            UPDATE event_ip
            SET {$updateString}
            WHERE
                event_ip.id = :entity_id AND
                event_ip.key = :key
        ");

        tirreno('models')->ip->execQuery($query, $params);

        // update totals only after event_ip update!
        $ispIds = $this->slimIds([$previousIspId, $newIspId]);
        tirreno('models')->isp->updateTotalsByEntityIds($ispIds, $apiKey, true);

        $countryIds = $this->slimIds([$previousCountryId, $newCountryId]);
        tirreno('models')->country->updateTotalsByEntityIds($countryIds, $apiKey, true);
    }
}
