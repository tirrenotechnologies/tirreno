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

class Country extends \Tirreno\Entities\Single {
    protected int $id;
    protected string $name;
    protected string $iso;

    protected int $dataId;

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
        string $name,
        string $iso,
        int $dataId,
        ?int $totalVisit,
        ?int $totalIp,
        ?int $totalAccount,
        ?string $lastseen,
        string $created,
        ?string $updated,
        int $key,
    ) {
        $this->id           = $id;
        $this->name         = $name;
        $this->iso          = $iso;
        $this->dataId       = $dataId;
        $this->totalVisit   = $totalVisit;
        $this->totalIp      = $totalIp;
        $this->totalAccount = $totalAccount;
        $this->lastseen     = $lastseen;
        $this->created      = $created;
        $this->updated      = $updated;
        $this->key          = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Countries($key);

        return $model->where('country_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['country_id'],
            $data['country_name'],
            $data['country_iso'],
            $data['country_data_id'],
            $data['country_total_visit'],
            $data['country_total_ip'],
            $data['country_total_account'],
            $data['country_lastseen'],
            $data['country_created'],
            $data['country_updated'],
            $key,
        );
    }
}
