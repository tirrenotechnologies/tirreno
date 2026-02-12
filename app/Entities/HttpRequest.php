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

namespace Tirreno\Entities;

final class HttpRequest {
    private string $url;
    private string $method;

    /** @var array<int, string> */
    private array $headers;

    private ?string $body;
    private int $connectTimeoutSeconds;
    private int $timeoutSeconds;
    private bool $sslVerify;

    public function __construct(
        string $url,
        string $method,
        array $headers,
        ?string $body,
        int $connectTimeoutSeconds = 3,
        int $timeoutSeconds = 15,
        bool $sslVerify = true
    ) {
        $this->url = $url;
        $this->method = $method;
        $this->headers = $headers;
        $this->body = $body;
        $this->connectTimeoutSeconds = $connectTimeoutSeconds;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->sslVerify = $sslVerify;
    }

    public function url(): string {
        return $this->url;
    }

    public function method(): string {
        return $this->method;
    }

    public function headers(): array {
        return $this->headers;
    }

    public function body(): ?string {
        return $this->body;
    }

    public function connectTimeoutSeconds(): int {
        return $this->connectTimeoutSeconds;
    }

    public function timeoutSeconds(): int {
        return $this->timeoutSeconds;
    }

    public function sslVerify(): bool {
        return $this->sslVerify;
    }
}
