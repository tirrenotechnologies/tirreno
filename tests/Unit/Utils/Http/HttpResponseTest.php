<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Entities\HttpResponse;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Tirreno\Entities\HttpResponse
 */
final class HttpResponseTest extends TestCase {
    public function testSuccessResponse(): void {
        $code = 200;
        $body = '{"ok": true}';

        $headers = [
            'HTTP/1.1 200 OK',
            'Content-Type: application/json',
        ];

        $response = HttpResponse::success($code, $body, $headers);

        $this->assertTrue($response->ok());
        $this->assertSame($code, $response->code());
        $this->assertSame(['ok' => true], $response->body());
        $this->assertNull($response->error());
        $this->assertSame($headers, $response->headers());
    }

    public function testFailureResponse(): void {
        $code = 503;
        $error = 'service_unavailable';

        $headers = [
            'HTTP/1.1 503 Service Unavailable',
        ];

        $response = HttpResponse::failure($code, $error, $headers);

        $this->assertFalse($response->ok());
        $this->assertSame($code, $response->code());
        $this->assertNull($response->body());
        $this->assertSame($error, $response->error());
        $this->assertSame($headers, $response->headers());
    }
}
