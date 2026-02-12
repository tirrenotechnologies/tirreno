<?php

declare(strict_types=1);

namespace Tests\Support;

use Tirreno\Entities\HttpRequest;
use Tirreno\Entities\HttpResponse;
use Tirreno\Interfaces\HttpTransportInterface;

/**
 * FakeTransport is a deterministic test double for HttpTransportInterface.
 */
final class FakeTransport implements HttpTransportInterface {
    private bool $available;
    private HttpResponse $response;

    public int $requestCalls = 0;

    public function __construct(bool $available, HttpResponse $response) {
        $this->available = $available;
        $this->response = $response;
    }

    public function isAvailable(): bool {
        $result = $this->available;
        return $result;
    }

    public function request(HttpRequest $request): HttpResponse {
        $this->requestCalls = $this->requestCalls + 1;

        $result = $this->response;
        return $result;
    }
}
