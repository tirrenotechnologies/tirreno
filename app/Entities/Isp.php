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

namespace Tirreno\Entities;

class Isp extends \Tirreno\Entities\Single {
    protected int $id;
    protected int $asn;
    protected ?string $name;
    protected ?string $description;

    protected ?int $totalVisit;
    protected ?int $totalIp;
    protected ?int $totalAccount;
    protected ?string $lastseen;
    protected string $created;
    protected ?string $updated;

    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen', 'updated'];

    public function __construct(
        int $id,
        int $asn,
        ?string $name,
        ?string $description,
        ?int $totalVisit,
        ?int $totalIp,
        ?int $totalAccount,
        ?string $lastseen,
        string $created,
        ?string $updated,
        int $key,
    ) {
        $this->id           = $id;
        $this->asn          = $asn;
        $this->name         = $name;
        $this->description  = $description;
        $this->totalVisit   = $totalVisit;
        $this->totalIp      = $totalIp;
        $this->totalAccount = $totalAccount;
        $this->lastseen     = $lastseen;
        $this->created      = $created;
        $this->updated      = $updated;
        $this->key          = $key;
    }

    // TODO: tmp
    public static function getById(int $id, int $key): ?self {
        return null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['isp_id'],
            $data['isp_asn'],
            $data['isp_name'],
            $data['isp_description'],
            $data['isp_total_visit'],
            $data['isp_total_ip'],
            $data['isp_total_account'],
            $data['isp_lastseen'],
            $data['isp_created'],
            $data['isp_updated'],
            $key,
        );
    }
}
