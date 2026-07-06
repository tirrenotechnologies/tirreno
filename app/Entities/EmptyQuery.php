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
class EmptyQuery extends \Tirreno\Entities\BaseEmpty {
    protected ?int $id = null;
    protected ?string $query = null;
    protected \Tirreno\Entities\Resource|\Tirreno\Entities\EmptyResource $resource;

    protected ?string $lastseen = null;
    protected ?string $created = null;

    protected ?int $key = null;

    protected array $nestedProps = ['resource'];
    protected array $tsFields = ['created', 'lastseen'];

    protected function setAdditional(): void {
        $this->resource = tirreno('entities')->emptyResource->get();
    }
}
