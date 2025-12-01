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

namespace Utils;

class Network {
    public static function safeFileGetContents(string $path, ?array $options): array {
        set_error_handler([\Utils\ErrorHandler::class, 'exceptionErrorHandler']);

        $result = null;

        try {
            $context = null;
            if ($options) {
                $context = stream_context_create($options);
            }

            $result = file_get_contents($path, false, $context);
        } catch (\Throwable $e) {
            return [
                'content'   => null,
                'headers'   => [],
            ];
        }

        restore_error_handler();

        return [
            'content'   => $result !== false ? $result : null,
            'headers'   => isset($http_response_header) ? $http_response_header : [],
        ];
    }

    public static function sendApiRequest(?array $data, string $path, string $method, ?string $enrichmentKey): array {
        $version = \Utils\VersionControl::versionString();
        $userAgent = \Base::instance()->get('APP_USER_AGENT');
        $userAgent = ($version && $userAgent) ? $userAgent . '/' . $version : $userAgent;

        $url = \Utils\Variables::getEnrichmentApi() . $path;

        $headers = [
            'User-Agent: ' . $userAgent,
        ];

        if ($enrichmentKey !== null) {
            //$enrichmentKey = \Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();
            $headers[] = 'Authorization: Bearer ' . $enrichmentKey;
        }

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            $data = json_encode($data);
        }

        $resp = self::proceedRequest($data, $url, $headers, $method);

        return $resp;
    }

    public static function proceedRequest(?string $data, string $url, array $headers, string $method, bool $ssl = true): array {
        $code = null;
        $response = null;
        $error = null;

        if (function_exists('curl_init')) {
            // curl
            $ch = curl_init($url);
            if ($ch === false) {
                return [null, null];
            }

            curl_setopt_array($ch, [
                CURLOPT_POST            => true,
                CURLOPT_HTTPHEADER      => $headers,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_CONNECTTIMEOUT  => 30,
                CURLOPT_TIMEOUT         => 30,
            ]);

            if (!$ssl) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }

            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
            } else {
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            }

            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                $error = curl_error($ch);
                $response = null;
            }

            curl_close($ch);
        } else {
            // stream
            $options = [
                'http' => [
                    'method'    => $method,
                    'header'    => implode("\r\n", $headers),
                    'timeout'   => 30,
                ],
            ];

            if (!$ssl) {
                $options['ssl'] = [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ];
            }

            if ($data !== null) {
                $options['http']['content'] = $data;
            }

            $result = self::safeFileGetContents($url, $options);

            $response = $result['content'];
            $responseHeaders = $result['headers'];

            $code = null;

            if (isset($responseHeaders[0])) {
                preg_match('{HTTP/\d\.\d\s+(\d+)}', $responseHeaders[0], $match);
                $code = \Utils\Conversion::intValCheckEmpty($match[1], 0);
            }
        }

        $resp = [
            'code'  => $code,
            'data'  => $response !== null ? json_decode($response, true) : [],
            'error' => $error,
        ];

        return $resp;
    }
}
