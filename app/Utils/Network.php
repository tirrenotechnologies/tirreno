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

namespace Tirreno\Utils;

class Network {
    public static function sendApiRequest(?array $data, string $path, string $method, ?string $enrichmentKey, ?int $apiKey = null): \Tirreno\Entities\HttpResponse {
        $version = tirreno('utils')->versionControl->versionString();
        $userAgent = tirreno('storage')->get('APP_USER_AGENT');
        $userAgent = ($version && $userAgent) ? $userAgent . '/' . $version : $userAgent;

        $url = tirreno('utils')->variables->getEnrichmentApi() . $path;

        $headers = [
            'User-Agent: ' . $userAgent,
        ];

        if ($enrichmentKey !== null) {
            $headers[] = 'Authorization: Bearer ' . $enrichmentKey;
        }

        $body = null;
        if ($data !== null) {
            $body = json_encode($data);
            if ($body === false) {
                return tirreno('entities')->httpResponse->failure(null, 'json_encode_failed', []);
            }
        }

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            $data = json_encode($data);
        }

        $request = tirreno('entities')->httpRequest->create($url, $method, $headers, $data);
        $client = tirreno('utils')->httpClient->default();

        return $client->request($request, $apiKey);
    }
}
