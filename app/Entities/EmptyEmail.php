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
class EmptyEmail extends \Tirreno\Entities\BaseEmpty {
    protected ?int $id = null;
    protected ?string $email = null;
    protected ?bool $fraud = null;
    protected ?bool $checked = null;

    protected ?string $lastseen = null;
    protected ?string $created = null;
    protected ?int $key = null;

    protected array $nestedProps = [];
    protected array $tsFields = ['created', 'lastseen'];
}
