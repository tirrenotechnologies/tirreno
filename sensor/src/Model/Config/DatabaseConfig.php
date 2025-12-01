<?php

/**
 * tirreno ~ open security analytics
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

namespace Sensor\Model\Config;

class DatabaseConfig implements \JsonSerializable {
    public function __construct(
        public string $dbHost,
        public int $dbPort,
        public string $dbUser,
        #[\SensitiveParameter]
        public string $dbPassword,
        public string $dbDatabaseName,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array {
        return [
            'dbHost' => $this->dbHost,
            'dbPort' => $this->dbPort,
            'dbUser' => $this->dbUser,
            'dbDatabaseName' => $this->dbDatabaseName,
        ];
    }
}
