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

class Payload extends \Tirreno\Entities\Single {
    protected int $id;
    protected ?array $payload;
    protected string $created;
    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created'];

    public function __construct(
        int $id,
        ?array $payload,
        string $created,
        int $key,
    ) {
        $this->id           = $id;
        $this->payload      = $payload;
        $this->created      = $created;
        $this->key          = $key;
    }

    public static function getById(int $id, int $key): ?self {
        $model = new \Tirreno\Models\Query\Payloads($key);

        return $model->where('payload_id', '=', $id)->get()->data[0] ?? null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['payload_id'],
            $data['payload_payload'] !== null ? json_decode($data['payload_payload']) : null,
            $data['payload_created'],
            $key,
        );
    }
}
