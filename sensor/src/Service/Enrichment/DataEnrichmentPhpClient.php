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

namespace Sensor\Service\Enrichment;

use Sensor\Exception\AuthException;
use Sensor\Exception\ForbiddenException;
use Sensor\Exception\RateLimitException;

/**
 * @phpstan-import-type EnrichmentClientResponse from DataEnrichmentClientInterface
 */
class DataEnrichmentPhpClient implements DataEnrichmentClientInterface {
    public function __construct(
        private string $baseUrl,
        private ?string $userAgent,
    ) {
    }

    public function query(array $data, string $token): array {
        $options = [
            'http' => [
                'method'    => 'POST',
                //'header' => sprintf("Authorization: Bearer %s\r\nContent-Type: application/json", $this->apiKey),
                'header'    => sprintf(
                    "Authorization: Bearer %s\r\n" .
                    "Content-Type: application/json\r\n" .
                    "User-Agent: %s\r\n",
                    $token,
                    $this->userAgent
                ),
                'content'   => json_encode($data, \JSON_THROW_ON_ERROR),
                'timeout'   => 30,
            ],
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($this->baseUrl . '/query', false, $context);
        if ($response === false) {
            if (isset($http_response_header[0])) {
                preg_match('{HTTP/\d\.\d\s+(\d+)}', $http_response_header[0], $match);
                $httpCode = intval($match[1]);

                // Handle unauthorized status
                if ($httpCode === 401) {
                    throw new AuthException('Access denied', $httpCode);
                }

                if ($httpCode === 403) {
                    throw new ForbiddenException('Forbidden', $httpCode);
                }

                if ($httpCode === 429) {
                    throw new RateLimitException('Rate limit', $httpCode);
                }

                if ($httpCode >= 400) {
                    throw new \RuntimeException(sprintf('Enrichment API returned HTTP code %d', $httpCode));
                }
            }

            throw new \RuntimeException('Error with HTTP request');
        }

        /** @var EnrichmentClientResponse $data */
        $data = json_decode($response, true);

        return $data;
    }

    public function track(string $token): void {
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => sprintf(
                    "Authorization: Bearer %s\r\n" .
                    "User-Agent: %s\r\n",
                    $token,
                    $this->userAgent,
                ),
            ],
        ];

        $context = stream_context_create($options);
        file_get_contents($this->baseUrl . '/track', false, $context);
    }
}
