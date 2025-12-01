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

namespace Sensor\Factory;

use Sensor\Model\Enriched\EnrichedData;
use Sensor\Model\Enriched\DomainEnriched;
use Sensor\Model\Enriched\DomainNotFoundEnriched;
use Sensor\Model\Enriched\DomainEnrichFailed;
use Sensor\Model\Enriched\EmailEnriched;
use Sensor\Model\Enriched\EmailEnrichFailed;
use Sensor\Model\Enriched\IpAddressEnriched;
use Sensor\Model\Enriched\IpAddressLocalhostEnriched;
use Sensor\Model\Enriched\IpAddressEnrichFailed;
use Sensor\Model\Enriched\IspEnriched;
use Sensor\Model\Enriched\IspEnrichedEmpty;
use Sensor\Model\Enriched\IspEnrichedLocalhost;
use Sensor\Model\Enriched\PhoneEnriched;
use Sensor\Model\Enriched\PhoneInvalidEnriched;
use Sensor\Model\Enriched\PhoneEnrichFailed;
use Sensor\Service\Enrichment\DataEnrichmentClientInterface;
use Sensor\Service\Logger;

/**
 * @phpstan-import-type EnrichmentClientResponse from DataEnrichmentClientInterface
 */
class EnrichedDataFactory {
    private const IP_IS_BOGON_ERROR_TEXT = 'IP is bogon';
    private const VALIDATION_ERROR_TEXT = 'Validation failed';
    private const SERVER_ERROR = 'Server Error';

    public function __construct(
        private Logger $logger,
    ) {
    }

    /**
     * @phpstan-param EnrichmentClientResponse $data
     */
    public function createFromResponse(array $data, array $origin): EnrichedData {
        $email = null;
        if (isset($data['email']['email'])) {
            try {
                $earliestBreach = $data['email']['earliest_breach'] !== null ? new \DateTimeImmutable($data['email']['earliest_breach']) : null;
                $email = new EmailEnriched(
                    $data['email']['email'],
                    $data['email']['blockemails'],
                    $data['email']['data_breach'],
                    $data['email']['data_breaches'],
                    $earliestBreach,
                    $data['email']['profiles'],
                    $data['email']['domain_contact_email'],
                    $data['email']['alert_list'],
                );
            } catch (\Throwable $e) {
                $this->logger->logWarning('Error during parsing email response', $e);
            }
        } elseif (isset($data['email']['value'])) {
            $email = new EmailEnrichFailed(
                $data['email']['error'] === self::SERVER_ERROR ? $origin['email'] : $data['email']['value'],
                $data['email']['error'] === self::VALIDATION_ERROR_TEXT,    // checked must be true on validation error to prevent repeating requests
            );
        }

        $domain = null;
        if (isset($data['domain']['domain']) && array_key_exists('ip', $data['domain'])) {
            try {
                $domain = new DomainEnriched(
                    $data['domain']['domain'],
                    $data['domain']['blockdomains'],
                    $data['domain']['disposable_domains'],
                    $data['domain']['free_email_provider'],
                    $data['domain']['ip'],
                    $data['domain']['geo_ip'],
                    $data['domain']['geo_html'],
                    $data['domain']['web_server'],
                    $data['domain']['hostname'],
                    $data['domain']['emails'],
                    $data['domain']['phone'],
                    $data['domain']['discovery_date'],
                    $data['domain']['tranco_rank'],
                    $data['domain']['creation_date'],
                    $data['domain']['expiration_date'],
                    $data['domain']['return_code'],
                    $data['domain']['disabled'],
                    $data['domain']['closest_snapshot'],
                    $data['domain']['mx_record'],
                );
            } catch (\Throwable $e) {
                $this->logger->logWarning('Error during parsing domain response', $e);
            }
        } elseif (isset($data['domain']['domain'])) {
            $domain = new DomainNotFoundEnriched(
                $data['domain']['domain'],
                $data['domain']['blockdomains'],
                $data['domain']['disposable_domains'],
                $data['domain']['free_email_provider'],
                $data['domain']['creation_date'],
                $data['domain']['expiration_date'],
                $data['domain']['return_code'],
                $data['domain']['disabled'],
                $data['domain']['closest_snapshot'],
                $data['domain']['mx_record'],
            );
        } elseif (isset($data['domain']['value'])) {
            $domain = new DomainEnrichFailed(
                $data['domain']['error'] === self::SERVER_ERROR ? $origin['domain'] : $data['domain']['value'],
                $data['domain']['error'] === self::VALIDATION_ERROR_TEXT,    // checked must be true on validation error to prevent repeating requests
            );
        }

        $ip = $isp = null;
        if (isset($data['ip']['ip'])) {
            try {
                $ip = new IpAddressEnriched(
                    $data['ip']['ip'],
                    $data['ip']['country'],
                    $data['ip']['hosting'],
                    $data['ip']['vpn'],
                    $data['ip']['tor'],
                    $data['ip']['relay'],
                    $data['ip']['starlink'],
                    $data['ip']['blocklist'],
                    $data['ip']['domains_count'],
                    $data['ip']['cidr'],
                    $data['ip']['alert_list'],
                );

                if (isset($data['ip']['asn'])) {
                    $isp = new IspEnriched(
                        $data['ip']['asn'],
                        $data['ip']['name'],
                        $data['ip']['description'],
                    );
                } else {
                    $isp = new IspEnrichedEmpty();
                }
            } catch (\Throwable $e) {
                $this->logger->logWarning('Error during parsing IP response', $e);
            }
        } elseif (isset($data['ip']['error'])) {
            if ($data['ip']['error'] === self::IP_IS_BOGON_ERROR_TEXT) {
                $ip = new IpAddressLocalhostEnriched(
                    $data['ip']['value'],
                );
                $isp = new IspEnrichedLocalhost();
            } else {
                $ip = new IpAddressEnrichFailed(
                    $data['ip']['error'] === self::SERVER_ERROR ? $origin['ip'] : $data['ip']['value'],
                    $data['ip']['error'] === self::VALIDATION_ERROR_TEXT,    // checked must be true on validation error to prevent repeating requests
                );
                $isp = new IspEnrichedEmpty();
            }
        }

        $phone = null;
        if (isset($data['phone']['phone_number']) && isset($data['phone']['profiles'])) {
            try {
                $phone = new PhoneEnriched(
                    $data['phone']['phone_number'],
                    $data['phone']['profiles'],
                    $data['phone']['iso_country_code'],
                    $data['phone']['calling_country_code'],
                    $data['phone']['national_format'],
                    $data['phone']['invalid'],
                    $data['phone']['validation_error'],
                    $data['phone']['carrier_name'],
                    $data['phone']['type'],
                    $data['phone']['alert_list'],
                );
            } catch (\Throwable $e) {
                $this->logger->logWarning('Error during parsing phone response', $e);
            }
        } elseif (isset($data['phone']['validation_error'])) {
            $this->logger->logWarning('Error getting phone from Enrichment API: ' . json_encode($data['phone']));
            $phone = new PhoneInvalidEnriched(
                $data['phone']['phone_number'],
                $data['phone']['invalid'],
                $data['phone']['validation_error'],
            );
        } elseif (isset($data['phone']['value'])) {
            $phone = new PhoneEnrichFailed(
                $data['phone']['error'] === self::SERVER_ERROR ? $origin['phone'] : $data['phone']['value'],
                $data['phone']['error'] === self::VALIDATION_ERROR_TEXT,    // checked must be true on validation error to prevent repeating requests
            );
        }

        // Check/log errors
        foreach ($data as $key => $value) {
            if (isset($value['error'])) {
                $this->logger->logWarning(sprintf(
                    'Error getting %s from Enrichment API: %s',
                    $key,
                    json_encode($value),
                ));
            }
        }

        return new EnrichedData($email, $domain, $ip, $isp, $phone, true);
    }
}
