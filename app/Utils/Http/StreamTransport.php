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

final class StreamTransport implements \Tirreno\Interfaces\HttpTransportInterface {
    public function isAvailable(): bool {
        return function_exists('file_get_contents');
    }

    public function request(\Tirreno\Entities\HttpRequest $request): \Tirreno\Entities\HttpResponse {
        $options = [
            'http' => [
                'method' => $request->method(),
                'header' => implode("\r\n", $request->headers()),
                'timeout' => $request->timeoutSeconds(),
            ],
        ];

        if (!$request->sslVerify()) {
            $options['ssl'] = [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ];
        }

        $body = $request->body();
        if ($body !== null) {
            $options['http']['content'] = $body;
        }

        $resultArr = $this->safeFileGetContents($request->url(), $options);

        $raw = $resultArr['content'];
        $respHeaders = $resultArr['headers'];

        $code = $this->extractHttpStatus($respHeaders);

        if ($raw === null) {
            $result = \Tirreno\Entities\HttpResponse::failure($code, 'stream_request_failed', $respHeaders);

            return $result;
        }

        return \Tirreno\Entities\HttpResponse::success($code, $raw, $respHeaders);
    }

    private function safeFileGetContents(string $url, ?array $options): array {
        set_error_handler([\Tirreno\Utils\ErrorHandler::class, 'exceptionErrorHandler']);

        try {
            $context = null;
            if ($options) {
                $context = stream_context_create($options);
            }

            $content = file_get_contents($url, false, $context);
        } catch (\Throwable $e) {
            restore_error_handler();

            return [
                'content' => null,
                'headers' => [],
            ];
        }

        restore_error_handler();

        $result = [
            'content' => $content !== false ? strval($content) : null,
            'headers' => $GLOBALS['http_response_header'] ?? [],
        ];

        return $result;
    }


    private function extractHttpStatus(array $headers): ?int {
        if (!isset($headers[0])) {
            return null;
        }

        $first = strval($headers[0]);
        preg_match('{HTTP/\d\.\d\s+(\d+)}', $first, $match);

        $value = $match[1] ?? null;
        if (!is_string($value)) {
            return null;
        }

        $result = intval($value);

        return $result;
    }
}
