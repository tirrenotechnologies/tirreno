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

namespace Sensor\Model\Enriched;

class DomainEnriched {
    public function __construct(
        public string $domain,
        public bool $blockdomains,
        public bool $disposableDomains,
        public bool $freeEmailProvider,
        public ?string $ip,
        public ?string $geoIp,
        public ?string $geoHtml,
        public ?string $webServer,
        public ?string $hostname,
        public ?string $emails,
        public ?string $phone,
        public string $discoveryDate,
        public ?int $trancoRank,
        public ?string $creationDate,
        public ?string $expirationDate,
        public ?int $returnCode,
        public bool $disabled,
        public ?string $closestSnapshot,
        public bool $mxRecord,
    ) {
    }
}
