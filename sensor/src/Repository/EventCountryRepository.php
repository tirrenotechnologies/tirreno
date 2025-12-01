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

namespace Sensor\Repository;

use Sensor\Dto\InsertCountryDto;
use Sensor\Entity\CountryEntity;
use Sensor\Model\Validated\Timestamp;

class EventCountryRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function insert(CountryEntity $country): InsertCountryDto {
        $sql = 'INSERT INTO event_country
                (key, country, lastseen, updated, created)
            VALUES
                (:key, :country, :lastseen, :updated, :created)
            ON CONFLICT (key, country) DO UPDATE
            SET
                lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $country->apiKeyId);
        $stmt->bindValue(':country', $country->countryId);
        $stmt->bindValue(':lastseen', $country->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $country->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $country->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        return new InsertCountryDto($result['id']);
    }
}
