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

abstract class Single extends Base {
    protected int $id;

    protected int $key;

    protected array $tsFields;
    protected array $nestedProps;

    abstract public static function getById(int $id, int $key): ?self;

    abstract public static function getFromQuery(array $data, int $key): self;

    // TODO: save() method for editing
    /*public function save(): void {
        if (!$this->modified) {
            return;
        }

        tirreno('models')->user->updateById(
            $this->id,
            $this->userid,
            $this->lastseen,
            ...
            $this->localizedTs, // override timestamps or not
            $this->key,
        );

        $this->modified = false;
    }*/
}
