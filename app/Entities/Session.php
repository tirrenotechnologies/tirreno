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

class Session extends \Tirreno\Entities\Single {
    protected int $id;
    protected int $userId;

    protected ?int $totalVisit;
    protected ?int $totalDevice;
    protected ?int $totalIp;
    protected ?int $totalCountry;

    protected string $lastseen;
    protected string $created;
    protected string $updated;

    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen', 'updated'];

    public function __construct(
        int $id,
        int $userId,
        ?int $totalVisit,
        ?int $totalDevice,
        ?int $totalIp,
        ?int $totalCountry,
        string $lastseen,
        string $created,
        string $updated,
        int $key,
    ) {
        $this->id               = $id;
        $this->userId           = $userId;
        $this->totalVisit       = $totalVisit;
        $this->totalDevice      = $totalDevice;
        $this->totalIp          = $totalIp;
        $this->totalCountry     = $totalCountry;
        $this->lastseen         = $lastseen;
        $this->created          = $created;
        $this->updated          = $updated;
        $this->key              = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Sessions($key);

        return $model->where('session_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['session_id'],
            $data['session_account_id'],
            $data['session_total_visit'],
            $data['session_total_device'],
            $data['session_total_ip'],
            $data['session_total_country'],
            $data['session_lastseen'],
            $data['session_created'],
            $data['session_updated'],
            $key,
        );
    }
}
