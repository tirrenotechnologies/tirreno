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

use Sensor\Dto\InsertIpAddressDto;
use Sensor\Entity\IpAddressLocalhostEnrichedEntity;
use Sensor\Entity\IpAddressEnrichedEntity;
use Sensor\Entity\IpAddressEntity;
use Sensor\Entity\IspLocalhostEntity;
use Sensor\Model\Validated\IpAddress;
use Sensor\Model\Validated\Timestamp;

class IpAddressRepository {
    public function __construct(
        private IspRepository $ispRepository,
        private \PDO $pdo,
    ) {
    }

    public function existsForApiKey(
        string $ipAddress,
        int $apiKeyId,
    ): bool {
        $sql = 'SELECT 1 FROM event_ip WHERE key = :key AND ip = :ip AND checked = :checked LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $apiKeyId);
        $stmt->bindValue(':ip', $ipAddress);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);

        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }

    public function insertSwitch(IpAddressEnrichedEntity|IpAddressLocalhostEnrichedEntity|IpAddressEntity $ip): InsertIpAddressDto {
        return $ip instanceof IpAddressEntity ? $this->insert($ip) : $this->insertEnriched($ip);
    }

    // enrichment failed || enriment off || already enriched
    public function insert(IpAddressEntity $ipAddress): InsertIpAddressDto {
        // if `checked` === null -> enrichment was not performed; `checked` === false -> enrichment failed; `checked` === true -> if ip is bogon
        // set checked true if ipAddress is invalid
        $ipAddress->checked = IpAddress::isInvalid($ipAddress->ipAddress) ? true : $ipAddress->checked;

        $sql = 'INSERT INTO event_ip
                (key, ip, hash, country, fraud_detected, lastseen, created, updated, checked)
            VALUES
                (:key, :ip, :hash, :country, :fraud_detected, :lastseen, :created, :updated, COALESCE(:checked, false))
            ON CONFLICT (key, ip) DO UPDATE
            SET
                hash = EXCLUDED.hash, fraud_detected = EXCLUDED.fraud_detected,
                lastseen = EXCLUDED.lastseen, checked = COALESCE(:checked, event_ip.checked)
            RETURNING id, country, isp, checked';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $ipAddress->apiKeyId);
        $stmt->bindValue(':ip', $ipAddress->ipAddress);
        $stmt->bindValue(':hash', $ipAddress->hash);
        $stmt->bindValue(':country', 0);
        $stmt->bindValue(':fraud_detected', $ipAddress->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', $ipAddress->checked, $ipAddress->checked === null ? \PDO::PARAM_NULL : \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $ipAddress->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $ipAddress->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $ipAddress->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int, country: int, isp: ?int, checked: ?bool} $result */
        $result = $stmt->fetch();

        // update existing isp; else insert or update null-isp and set it for ip
        if ($result['isp'] !== null) {
            $this->ispRepository->update($ipAddress->isp, $result['isp']);
        } else {
            // ip is localhost; replace n/a with localhost
            if ($result['checked']) {
                $ipAddress->isp = new IspLocalhostEntity($ipAddress->apiKeyId, $ipAddress->lastSeen);
            }

            // existing ip; isp is null
            // new ip; enrichment off/failed
            $ispId = $this->ispRepository->insert($ipAddress->isp);

            $sql = 'UPDATE event_ip
                SET isp = :isp_id
                WHERE id = :id AND key = :key
                RETURNING id, country, isp';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':key', $ipAddress->apiKeyId);
            $stmt->bindValue(':id', $result['id']);
            $stmt->bindValue(':isp_id', $ispId);
            $stmt->execute();

            /** @var array{id: int, country: int, isp: int} $result */
            $result = $stmt->fetch();
        }

        return new InsertIpAddressDto($result['id'], $result['country'], $result['isp']);
    }

    public function insertEnriched(IpAddressEnrichedEntity|IpAddressLocalhostEnrichedEntity $ipAddress): InsertIpAddressDto {
        $ispId = $ipAddress->isp !== null ? $this->ispRepository->insert($ipAddress->isp) : null;

        $sql = 'INSERT INTO event_ip (
                key, ip, hash, country, vpn, tor, relay, starlink, blocklist, domains_count, cidr, data_center, isp,
                alert_list, checked, fraud_detected, lastseen, created, updated
            ) VALUES (
                :key, :ip, :hash, :country, :vpn, :tor, :relay, :starlink, :blocklist, :domains_count, :cidr, :data_center, :isp,
                :alert_list, :checked, :fraud_detected, :lastseen, :created, :updated
            ) ON CONFLICT (key, ip) DO UPDATE
            SET hash = EXCLUDED.hash, country = EXCLUDED.country, vpn = EXCLUDED.vpn, tor = EXCLUDED.tor, relay = EXCLUDED.relay, starlink = EXCLUDED.starlink,
                blocklist = EXCLUDED.blocklist, domains_count = EXCLUDED.domains_count, cidr = EXCLUDED.cidr, data_center = EXCLUDED.data_center,
                isp = EXCLUDED.isp, alert_list = EXCLUDED.alert_list, checked = EXCLUDED.checked, fraud_detected = EXCLUDED.fraud_detected,
                lastseen = EXCLUDED.lastseen
            RETURNING id, country, isp';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':key', $ipAddress->apiKeyId);
        $stmt->bindValue(':ip', $ipAddress->ipAddress);
        $stmt->bindValue(':hash', $ipAddress->hash);
        $stmt->bindValue(':country', $ipAddress->countryId);
        $stmt->bindValue(':vpn', $ipAddress->vpn, \PDO::PARAM_BOOL);
        $stmt->bindValue(':tor', $ipAddress->tor, \PDO::PARAM_BOOL);
        $stmt->bindValue(':relay', $ipAddress->relay, \PDO::PARAM_BOOL);
        $stmt->bindValue(':starlink', $ipAddress->starlink, \PDO::PARAM_BOOL);
        $stmt->bindValue(':blocklist', $ipAddress->blocklist, \PDO::PARAM_BOOL);
        $stmt->bindValue(':domains_count', json_encode($ipAddress->domainsCount, \JSON_THROW_ON_ERROR));
        $stmt->bindValue(':cidr', $ipAddress->cidr);
        $stmt->bindValue(':data_center', $ipAddress->hosting, \PDO::PARAM_BOOL);
        $stmt->bindValue(':isp', $ispId);
        $stmt->bindValue(':alert_list', $ipAddress->alertList, \PDO::PARAM_BOOL);
        $stmt->bindValue(':checked', true, \PDO::PARAM_BOOL);
        $stmt->bindValue(':fraud_detected', $ipAddress->fraudDetected, \PDO::PARAM_BOOL);
        $stmt->bindValue(':lastseen', $ipAddress->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':created', $ipAddress->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->bindValue(':updated', $ipAddress->lastSeen->format(Timestamp::EVENTFORMAT));
        $stmt->execute();

        /** @var array{id: int, country: int, isp: ?int} $result */
        $result = $stmt->fetch();

        return new InsertIpAddressDto($result['id'], $result['country'], $result['isp']);
    }
}
