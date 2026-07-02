<?php

/**
 * tirreno ~ open-source security framework
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

namespace Tirreno\Utils\Http;

class HttpClient {
    private array $transports;

    public function __construct(array $transports) {
        $this->transports = $transports;
    }

    public static function default(): self {
        $transports = [
            \Tirreno\Utils\Http\CurlTransport::class,
            \Tirreno\Utils\Http\StreamTransport::class,
        ];

        return new self($transports);
    }

    public function request(\Tirreno\Entities\HttpRequest $request, ?int $apiKey = null): \Tirreno\Entities\HttpResponse {
        $response = null;

        $time = new \DateTime();
        $milliseconds = intval(intval($time->format('u')) / 1000);
        $time = $time->format('Y-m-d H:i:s') . '.' . sprintf('%03d', $milliseconds);

        foreach ($this->transports as $transport) {
            if ($transport::isAvailable()) {
                $response = $transport::request($request);
                break;
            }
        }

        $response = $response ?: tirreno('entities')->httpResponse->failure(null, 'no_transport_available', []);

        $this->saveLogbook($request, $response, $time, $apiKey);

        return $response;
    }

    private function saveLogbook(\Tirreno\Entities\HttpRequest $request, \Tirreno\Entities\HttpResponse $response, string $startTime, ?int $apiKey): void {
        tirreno('entities')->logbook->addRecord(
            $request->url,
            $startTime,                                     //$started,
            null,                                           //$ip,
            null,                                           //$eventId,
            $response->error,                               //$errorText,
            $response->body ? json_encode($response->body) : null,  //$raw,
            $apiKey,
            $response->ok ? tirreno('utils')->constants->LOGBOOK_ERROR_TYPE_SUCCESS : tirreno('utils')->constants->LOGBOOK_ERROR_TYPE_CRITICAL_ERROR,
            //$ended,
        );
    }
}
