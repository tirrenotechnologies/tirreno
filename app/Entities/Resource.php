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

class Resource extends \Tirreno\Entities\Single {
    protected int $id;
    protected string $url;
    protected ?string $title;
    protected ?int $httpCode;

    protected ?int $totalVisit;
    protected ?int $totalIp;
    protected ?int $totalDevice;
    protected ?int $totalAccount;
    protected ?int $totalCountry;
    protected ?int $totalEdit;

    protected string $lastseen;
    protected string $created;
    protected string $updated;

    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen', 'updated'];

    public function __construct(
        int $id,
        string $url,
        ?string $title,
        ?int $httpCode,
        ?int $totalVisit,
        ?int $totalIp,
        ?int $totalDevice,
        ?int $totalAccount,
        ?int $totalCountry,
        ?int $totalEdit,
        string $lastseen,
        string $created,
        string $updated,
        int $key,
    ) {
        $this->id               = $id;
        $this->url              = $url;
        $this->title            = $title;
        $this->httpCode         = $httpCode;

        $this->totalVisit       = $totalVisit;
        $this->totalIp          = $totalIp;
        $this->totalDevice      = $totalDevice;
        $this->totalAccount     = $totalAccount;
        $this->totalCountry     = $totalCountry;
        $this->totalEdit        = $totalEdit;

        $this->lastseen         = $lastseen;
        $this->created          = $created;
        $this->updated          = $updated;

        $this->key              = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Urls($key);

        return $model->where('url_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['url_id'],
            $data['url_url'],
            $data['url_title'],
            $data['url_http_code'],
            $data['url_total_visit'],
            $data['url_total_ip'],
            $data['url_total_device'],
            $data['url_total_account'],
            $data['url_total_country'],
            $data['url_total_edit'],
            $data['url_lastseen'],
            $data['url_created'],
            $data['url_updated'],
            $key,
        );
    }
}
