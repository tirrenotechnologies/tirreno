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

namespace Sensor\Service\Enrichment;

use Sensor\Exception\AuthException;

/**
 * @phpstan-type ErrorResponse array{
 *     value: string,
 *     error: string
 * }
 * @phpstan-type InvalidPhoneResponse array{
 *     phone_number: string,
 *     invalid: bool,
 *     validation_error: string
 * }
 * @phpstan-type DomainNotFoundResponse array{
 *     domain: string,
 *     blockdomains: bool,
 *     disposable_domains: bool,
 *     free_email_provider: bool,
 *     creation_date: ?string,
 *     expiration_date: ?string,
 *     return_code: ?int,
 *     disabled: bool,
 *     closest_snapshot: ?string,
 *     mx_record: bool,
 * }
 * @phpstan-type DomainInfo array{
 *     blockdomains: bool,
 *     disposable_domains: bool,
 *     free_email_provider: bool,
 *     domain: string,
 *     ip: ?string,
 *     geo_ip: ?string,
 *     geo_html: ?string,
 *     web_server: ?string,
 *     hostname: ?string,
 *     emails: ?string,
 *     phone: ?string,
 *     discovery_date: string,
 *     tranco_rank: ?int,
 *     creation_date: ?string,
 *     expiration_date: ?string,
 *     return_code: ?int,
 *     disabled: bool,
 *     closest_snapshot: ?string,
 *     mx_record: bool,
 * }
 * @phpstan-type EmailInfo array{
 *     email: string,
 *     blockemails: bool,
 *     data_breach: bool,
 *     data_breaches: int,
 *     earliest_breach: ?string,
 *     profiles: int,
 *     domain_contact_email: bool,
 *     domain: string,
 *     alert_list: ?bool,
 * }
 * @phpstan-type IPInfo array{
 *     ip: string,
 *     country: string,
 *     asn: ?int,
 *     name: ?string,
 *     hosting: bool,
 *     vpn: bool,
 *     tor: bool,
 *     relay: bool,
 *     starlink: bool,
 *     description: ?string,
 *     blocklist: bool,
 *     domains_count: string[],
 *     cidr: string,
 *     alert_list: ?bool,
 * }
 * @phpstan-type PhoneInfo array{
 *     profiles: int,
 *     phone_number: string,
 *     iso_country_code: ?string,
 *     calling_country_code: int,
 *     national_format: string,
 *     invalid: bool,
 *     validation_error: string|null,
 *     carrier_name: ?string,
 *     type: string,
 *     alert_list: ?bool,
 * }
 * @phpstan-type BrowserInfo array{
 *     ua: string,
 *     device: string,
 *     browser_name: string,
 *     browser_version: string,
 *     os_name: string,
 *     os_version: string,
 *     modified: bool,
 *  }
 * @phpstan-type EnrichmentClientResponse array{
 *     domain?: DomainInfo|ErrorResponse|DomainNotFoundResponse|null,
 *     email?: EmailInfo|ErrorResponse|null,
 *     ip?: IPInfo|ErrorResponse|null,
 *     phone?: PhoneInfo|ErrorResponse|InvalidPhoneResponse|null,
 *     ua?: BrowserInfo|ErrorResponse|null,
 *     detail?: string,
 * }
 * @phpstan-type EnrichmentClientRequest array{
 *     email?: array{value: string, hash: ?string},
 *     ip?: array{value: string, hash: ?string},
 *     phone?: array{value: string, hash: ?string},
 *     domain?: string,
 *     ua?: string,
 * }
 */
interface DataEnrichmentClientInterface {
    /**
     * @param EnrichmentClientRequest $data
     * @param string $token
     *
     * @phpstan-return EnrichmentClientResponse
     *
     * @throws AuthException     if API key is invalid
     * @throws \RuntimeException
     */
    public function query(array $data, string $token): array;
}
