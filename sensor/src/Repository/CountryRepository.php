<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Sensor\Repository;

class CountryRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function getCountryIdByCode(string $code): int {
        $sql = 'SELECT id FROM countries WHERE iso = :iso LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':iso', $code);
        $stmt->execute();

        $result = $stmt->fetchColumn();

        return $result === false ? 0 : (int) $result;
    }
}
