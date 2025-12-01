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

        $result = $this->safeFileGetContents($this->baseUrl . '/query', $options);

        $response = $result['content'];
        $responseHeaders = $result['headers'];

        if ($response === null) {
            if (isset($responseHeaders[0])) {
                preg_match('{HTTP/\d\.\d\s+(\d+)}', $responseHeaders[0], $match);
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

    private function safeFileGetContents(string $path, ?array $options): array {
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);

            return true;
        });

        $result = null;

        try {
            $context = null;
            if ($options) {
                $context = stream_context_create($options);
            }
            $result = file_get_contents($path, false, $context);
        } catch (\Throwable $e) {
            return [
                'content' => null,
                'headers' => [],
            ];
        }

        restore_error_handler();

        return [
            'content'   => $result !== false ? $result : null,
            'headers'   => isset($http_response_header) ? $http_response_header : [],
        ];
    }
}
