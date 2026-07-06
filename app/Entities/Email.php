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
class Email extends \Tirreno\Entities\Single {
    protected int $id;
    protected string $email;
    protected bool $fraud;
    protected ?bool $checked;

    protected string $lastseen;
    protected string $created;
    protected int $key;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen'];

    public function __construct(
        int $id,
        string $email,
        bool $fraud,
        ?bool $checked,
        string $lastseen,
        string $created,
        int $key,
    ) {
        $this->id           = $id;
        $this->email        = $email;
        $this->fraud        = $fraud;
        $this->checked      = $checked;

        $this->lastseen     = $lastseen;
        $this->created      = $created;

        $this->key          = $key;
    }

    // TODO: tmp
    public static function getById(int $id, int $key): ?self {
        return null;
    }

    public static function getFromQuery(array $data, int $key): self {
        return new self(
            $data['email_id'],
            $data['email_email'],
            $data['email_fraud_detected'],
            $data['email_checked'],
            $data['email_lastseen'],
            $data['email_created'],
            $key,
        );
    }
}
