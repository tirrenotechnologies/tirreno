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

// TODO: add account id? missing half of the fields
class Phone extends \Tirreno\Entities\Single {
    protected int $id;
    protected string $phoneNumber;
    protected ?int $shared;
    protected bool $fraud;
    protected ?bool $invalid;
    protected ?bool $checked;

    protected string $lastseen;
    protected string $created;
    protected ?string $updated;
    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen', 'updated'];

    public function __construct(
        int $id,
        string $phoneNumber,
        ?int $shared,
        bool $fraud,
        ?bool $invalid,
        ?bool $checked,
        string $lastseen,
        string $created,
        ?string $updated,
        int $key,
    ) {
        $this->id           = $id;
        $this->phoneNumber  = $phoneNumber;
        $this->shared       = $shared;
        $this->fraud        = $fraud;
        $this->invalid      = $invalid;
        $this->checked      = $checked;

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
            $data['phone_id'],
            $data['phone_phone_number'],
            $data['phone_shared'],
            $data['phone_fraud_detected'],
            $data['phone_invalid'],
            $data['phone_checked'],
            $data['phone_lastseen'],
            $data['phone_created'],
            $data['phone_updated'],
            $key,
        );
    }
}
