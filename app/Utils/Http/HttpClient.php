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
    /** @var array<int, \Tirreno\Interfaces\HttpTransportInterface> */
    private array $transports;

    /**
     * @param array<int, \Tirreno\Interfaces\HttpTransportInterface> $transports
     */
    public function __construct(array $transports) {
        $this->transports = $transports;
    }

    public static function default(): self {
        $transports = [
            new \Tirreno\Utils\Http\CurlTransport(),
            new \Tirreno\Utils\Http\StreamTransport(),
        ];

        return new self($transports);
    }

    public function request(\Tirreno\Entities\HttpRequest $request): \Tirreno\Entities\HttpResponse {
        foreach ($this->transports as $transport) {
            if ($transport->isAvailable()) {
                return $transport->request($request);
            }
        }

        return \Tirreno\Entities\HttpResponse::failure(null, 'no_transport_available', []);
    }
}
