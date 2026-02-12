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
    public static function sendApiRequest(?array $data, string $path, string $method, ?string $enrichmentKey): \Tirreno\Entities\HttpResponse {
        $version = \Tirreno\Utils\VersionControl::versionString();
        $userAgent = \Base::instance()->get('APP_USER_AGENT');
        $userAgent = ($version && $userAgent) ? $userAgent . '/' . $version : $userAgent;

        $url = \Tirreno\Utils\Variables::getEnrichmentApi() . $path;

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
                return \Tirreno\Entities\HttpResponse::failure(null, 'json_encode_failed', []);
            }
        }

        $headers = \Tirreno\Utils\Http\HeaderUtils::ensureHeader($headers, 'Content-Type', 'application/json');

        if ($data !== null) {
            $headers[] = 'Content-Type: application/json';
            $data = json_encode($data);
        }

        $request = new \Tirreno\Entities\HttpRequest($url, $method, $headers, $data);
        $client = \Tirreno\Utils\Http\HttpClient::default();

        return $client->request($request);
    }
}
