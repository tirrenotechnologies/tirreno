<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Utils\Http\HttpClient;
use Tirreno\Entities\HttpRequest;
use Tirreno\Entities\HttpResponse;
use Tests\Support\FakeTransport;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Tirreno\Utils\Http\HttpClient
 */
final class HttpClientTest extends TestCase {
    public function testUsesFirstAvailableTransport(): void {
        $first = new FakeTransport(false, HttpResponse::failure(null, 'should_not_be_used', []));
        $second = new FakeTransport(true, HttpResponse::success(200, '["OK"]', []));

        $transports = [
            $first,
            $second,
        ];

        $client = new HttpClient($transports);

        $request = new HttpRequest(
            'https://example.com',
            'GET',
            [],
            null,
            1,
            1,
            true
        );

        $response = $client->request($request);

        $this->assertTrue($response->ok());
        $this->assertSame(200, $response->code());
        $this->assertSame(['OK'], $response->body());

        $this->assertSame(0, $first->requestCalls);
        $this->assertSame(1, $second->requestCalls);
    }

    public function testReturnsNoTransportAvailableWhenNoneAvailable(): void {
        $first = new FakeTransport(false, HttpResponse::success(200, 'OK', []));
        $second = new FakeTransport(false, HttpResponse::success(200, 'OK', []));

        $transports = [
            $first,
            $second,
        ];

        $client = new HttpClient($transports);

        $request = new HttpRequest(
            'https://example.com',
            'GET',
            [],
            null,
            1,
            1,
            true
        );

        $response = $client->request($request);

        $this->assertFalse($response->ok());
        $this->assertNull($response->code());
        $this->assertSame('no_transport_available', $response->error());

        $this->assertSame(0, $first->requestCalls);
        $this->assertSame(0, $second->requestCalls);
    }
}
