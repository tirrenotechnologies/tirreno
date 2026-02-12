<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Entities\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Tirreno\Entities\HttpRequest
 */
final class HttpRequestTest extends TestCase {
    public function testGettersReturnConstructorValues(): void {
        $url = 'https://example.com/api';
        $method = 'POST';

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        $body = '{"x":1}';

        $connectTimeoutSeconds = 5;
        $timeoutSeconds = 10;
        $sslVerify = false;

        $request = new HttpRequest(
            $url,
            $method,
            $headers,
            $body,
            $connectTimeoutSeconds,
            $timeoutSeconds,
            $sslVerify
        );

        $this->assertSame($url, $request->url());
        $this->assertSame($method, $request->method());
        $this->assertSame($headers, $request->headers());
        $this->assertSame($body, $request->body());
        $this->assertSame($connectTimeoutSeconds, $request->connectTimeoutSeconds());
        $this->assertSame($timeoutSeconds, $request->timeoutSeconds());
        $this->assertSame($sslVerify, $request->sslVerify());
    }
}
