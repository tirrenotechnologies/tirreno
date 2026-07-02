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

// NOTE: has nested entities
class Query extends \Tirreno\Entities\Single {
    protected int $id;
    protected string $query;
    protected \Tirreno\Entities\Resource $resource;

    protected string $lastseen;
    protected string $created;

    protected int $key;

    protected array $nestedProps = ['resource'];
    protected array $tsFields = ['created', 'lastseen'];

    public function __construct(
        int $id,
        string $query,
        \Tirreno\Entities\Resource $resource,
        string $lastseen,
        string $created,
        int $key,
    ) {
        $this->id       = $id;
        $this->query    = $query;
        $this->resource = $resource;

        $this->lastseen = $lastseen;
        $this->created  = $created;

        $this->key      = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Queries($key);

        return $model->where('url_query_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['url_query_id'],
            $data['url_query_query'],
            tirreno('entities')->resource->getFromQuery($data, $key),
            $data['url_query_lastseen'],
            $data['url_query_created'],
            $key,
        );
    }
}
