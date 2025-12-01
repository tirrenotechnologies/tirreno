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

use Sensor\Dto\InsertUserAgentDto;
use Sensor\Entity\UserAgentEnrichedEntity;
use Sensor\Entity\UserAgentEntity;
use Sensor\Model\Validated\Timestamp;

class UserAgentRepository {
    public function __construct(
        private \PDO $pdo,
    ) {
    }

    public function isChecked(
        string $userAgent,
        int $apiKeyId,
    ): bool {
        $sql = 'SELECT 1 FROM event_ua_parsed WHERE ua = :ua AND key = :key AND checked = :checked LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':ua', $userAgent);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);

        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function insertSwitch(UserAgentEnrichedEntity|UserAgentEntity $ua): InsertUserAgentDto {
        return $ua instanceof UserAgentEntity ? $this->insert($ua) : $this->insertEnriched($ua);
    }

    public function insert(UserAgentEntity $ua): InsertUserAgentDto {
        // if `checked` === null -> enrichment was not performed; `checked` === false -> enrichment failed;
        $stmt = null;
        // check manually for ua == null case
        if ($ua->userAgent === null) {
            $sql = 'SELECT id FROM event_ua_parsed WHERE ua IS NULL AND key = :key LIMIT 1';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':key', $ua->apiKeyId);
            $stmt->execute();
            /** @var array{id: int} $result */
            $result = $stmt->fetch();

            if ($result !== false) {
                $sql = 'UPDATE event_ua_parsed
                    SET checked = COALESCE(:checked, event_ua_parsed.checked)
                    WHERE key = :key AND id = :id
                    RETURNING id, os_name, os_version, browser_name, browser_version, device';
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':key', $ua->apiKeyId);
                $stmt->bindValue(':id', $result['id']);
                $stmt->bindValue(':checked', $ua->checked, $ua->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
            } else {
                $sql = 'INSERT INTO event_ua_parsed (key, ua, checked, created)
                    VALUES (:key, :ua, COALESCE(:checked, false), :created)
                    RETURNING id, os_name, os_version, browser_name, browser_version, device';
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':key', $ua->apiKeyId);
                $stmt->bindValue(':ua', $ua->userAgent);
                $stmt->bindValue(':checked', $ua->checked, $ua->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
                $stmt->bindValue(':created', $ua->lastSeen->format(Timestamp::EVENTFORMAT));
            }
        } else {
            $sql = 'INSERT INTO event_ua_parsed
                    (key, ua, checked, created)
                VALUES
                    (:key, :ua, COALESCE(:checked, false), :created)
                ON CONFLICT (key, ua) DO UPDATE
                SET
                    checked = COALESCE(:checked, event_ua_parsed.checked)
                RETURNING id, os_name, os_version, browser_name, browser_version, device';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':key', $ua->apiKeyId);
            $stmt->bindValue(':ua', $ua->userAgent);
            $stmt->bindValue(':checked', $ua->checked, $ua->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
            $stmt->bindValue(':created', $ua->lastSeen->format(Timestamp::EVENTFORMAT));
        }

        $stmt->execute();

        /** @var array{id: int, os_name: string, os_version: string, browser_name: string, browser_version: string, device: string} $result */
        $result = $stmt->fetch();

        return new InsertUserAgentDto($result['id'], $result['os_name'], $result['os_version'], $result['browser_name'], $result['browser_version'], $result['device']);
    }

    public function insertEnriched(UserAgentEnrichedEntity $ua): InsertUserAgentDto {
        $sql = 'INSERT INTO event_ua_parsed
                (key, ua, device, browser_name, browser_version, os_name, os_version, modified, checked, created)
            VALUES
                (:key, :ua, :device, :browser_name, :browser_version, :os_name, :os_version, :modified, :checked, :created)
            ON CONFLICT (key, ua) DO UPDATE
            SET
                device = EXCLUDED.device, browser_name = EXCLUDED.browser_name, browser_version = EXCLUDED.browser_version,
                os_name = EXCLUDED.os_name, os_version = EXCLUDED.os_version, modified = EXCLUDED.modified, checked = EXCLUDED.checked
            RETURNING id, os_name, os_version, browser_name, browser_version, device';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $ua->apiKeyId);
        $stmt->bindValue(':ua', $ua->userAgent);
        $stmt->bindValue(':device', $ua->device);
        $stmt->bindValue(':browser_name', $ua->browserName);
        $stmt->bindValue(':browser_version', $ua->browserVersion);
        $stmt->bindValue(':os_name', $ua->osName);
        $stmt->bindValue(':os_version', $ua->osVersion);
        $stmt->bindValue(':modified', $ua->modified, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':created', $ua->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int, os_name: string, os_version: string, browser_name: string, browser_version: string, device: string} $result */
        $result = $stmt->fetch();

        return new InsertUserAgentDto($result['id'], $result['os_name'], $result['os_version'], $result['browser_name'], $result['browser_version'], $result['device']);
    }

    // not used for now
    public function newerUserAgent(InsertUserAgentDto $newUa, InsertUserAgentDto $prevUa): int {
        // first check os_version
        $osCmp = $this->compareVersions($newUa->osVersion, $prevUa->osVersion);
        if ($osCmp !== 0) {
            return $osCmp == 1 ? $newUa->userAgentId : $prevUa->userAgentId;
        }

        $browserCmp = $this->compareVersions($newUa->browserVersion, $prevUa->browserVersion);
        if ($browserCmp !== 0) {
            return $browserCmp == 1 ? $newUa->userAgentId : $prevUa->userAgentId;
        }

        // should not occur
        return $newUa->userAgentId;
    }

    private function compareVersions(?string $version1, ?string $version2): int {
        if ($version1 === null) {
            return -1;
        }
        if ($version2 === null) {
            return 1;
        }

        $parts1 = explode('.', $version1);
        $parts2 = explode('.', $version2);
        $length = max(count($parts1), count($parts2));

        for ($i = 0; $i < $length; $i++) {
            $fp1 = preg_replace('/\D/', '', isset($parts1[$i]) ? $parts1[$i] : '');
            $fp2 = preg_replace('/\D/', '', isset($parts2[$i]) ? $parts2[$i] : '');

            $part1 = $fp1 === '' ? 0 : intval($fp1);
            $part2 = $fp2 === '' ? 0 : intval($fp2);

            if ($part1 > $part2) {
                return 1;
            } elseif ($part1 < $part2) {
                return -1;
            }
        }

        return 0;
    }
}
