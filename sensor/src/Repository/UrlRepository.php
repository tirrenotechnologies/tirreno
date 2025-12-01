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

use Sensor\Dto\InsertUrlDto;
use Sensor\Entity\UrlEntity;
use Sensor\Model\Validated\Timestamp;

class UrlRepository {
    public function __construct(
        private UrlQueryRepository $urlQueryRepository,
        private \PDO $pdo,
    ) {
    }

    public function insert(UrlEntity $url): InsertUrlDto {
        $sql = 'INSERT INTO event_url
                (key, url, title, http_code, lastseen, created, updated)
            VALUES
                (:key, :url, :title, :http_code, :lastseen, :created, :updated)
            ON CONFLICT (key, url) DO UPDATE
            SET
                title = EXCLUDED.title, http_code = EXCLUDED.http_code, lastseen = EXCLUDED.lastseen
            RETURNING id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $url->apiKeyId);
        $stmt->bindValue(':url', $url->url);
        $stmt->bindValue(':title', $url->title);
        $stmt->bindValue(':http_code', $url->httpCode);
        $stmt->bindValue(':lastseen', $url->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $url->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $url->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int} $result */
        $result = $stmt->fetch();

        $urlId = $result['id'];
        $queryId = $url->query !== null ? $this->urlQueryRepository->insert($url->query, $urlId) : null;

        return new InsertUrlDto($urlId, $queryId);
    }
}
