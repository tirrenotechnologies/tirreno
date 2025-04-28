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
class DataEnrichmentCurlClient implements DataEnrichmentClientInterface {
    public function __construct(
        private string $baseUrl,
        private ?string $userAgent,
    ) {
    }

    public function query(array $data, string $token): array {
        $ch = curl_init($this->baseUrl . '/query');
        if ($ch === false) {
            throw new \RuntimeException('Error cURL init');
        }

        $headers = [
            //'Authorization: Bearer '.$this->apiKey,
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'User-Agent: ' . $this->userAgent,
        ];

        $payload = json_encode($data);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        /** @var string $response */
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException('cURL Error: ' . $error);
        }

        curl_close($ch);

        /** @var EnrichmentClientResponse $data */
        $data = json_decode($response, true);

        if ($httpCode === 401) {
            $extra = $data['detail'] ?? 'Access denied';
            throw new AuthException($extra, $httpCode);
        }

        if ($httpCode === 403) {
            throw new ForbiddenException('Forbidden', $httpCode);
        }

        if ($httpCode === 429) {
            throw new RateLimitException('Rate limit', $httpCode);
        }

        if ($httpCode >= 400) {
            throw new \RuntimeException(sprintf('Enrichment API returned HTTP code %d: %s', $httpCode, $response));
        }

        return $data;
    }

    public function track(string $token): void {
        $ch = curl_init($this->baseUrl . '/track');
        if ($ch === false) {
            throw new \RuntimeException('Error cURL init');
        }

        $headers = ['Authorization: Bearer ' . $token];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, null);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_exec($ch);

        curl_close($ch);
    }
}
