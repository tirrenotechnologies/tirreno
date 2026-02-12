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

final class HttpResponse {
    private bool $ok;
    private ?int $code;
    private ?array $body;
    private ?string $error;

    /** @var array<int, string> */
    private array $headers;

    private function __construct(bool $ok, ?int $code, ?string $body, ?string $error, array $headers) {
        if ($body !== null) {
            $json = json_decode($body, true);
            $body = is_array($json) ? $json : [];
        }

        $this->ok = $ok;
        $this->code = $code;
        $this->body = $body;
        $this->error = $error;
        $this->headers = $headers;
    }

    public static function success(?int $code, ?string $body, array $headers): self {
        $result = new self(true, $code, $body, null, $headers);
        return $result;
    }

    public static function failure(?int $code, ?string $error, array $headers): self {
        $result = new self(false, $code, null, $error, $headers);
        return $result;
    }

    public function ok(): bool {
        return $this->ok;
    }

    public function code(): ?int {
        return $this->code;
    }

    public function body(): ?array {
        return $this->body;
    }

    public function error(): ?string {
        return $this->error;
    }

    public function headers(): array {
        return $this->headers;
    }
}
