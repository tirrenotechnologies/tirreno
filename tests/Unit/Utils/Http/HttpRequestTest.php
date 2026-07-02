<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Entities\HttpRequest;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for HttpRequest.
 *
 * Covered:
 * - constructor stores all provided values
 * - getters return constructor values unchanged
 *
 * Not covered:
 * - create() named constructor
 * - __get() magic accessor
 * - __set() magic mutator
 * - exception handling for unknown properties
 *
 * Notes:
 * - HttpRequest is currently a simple DTO/value object
 * - tests verify that data is preserved without modification
 *
 * @todo Refactor:
 * - consider making the object immutable (readonly properties)
 * - remove __get/__set magic methods if dynamic access is not required
 * - then constructor and factory coverage may be sufficient on their own
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

    public function testCreateReturnsEquivalentObject(): void {
        $request = HttpRequest::create(
            'https://example.com',
            'GET',
            [],
            null
        );

        $this->assertSame('https://example.com', $request->url());
        $this->assertSame('GET', $request->method());
    }

    public function testMagicGetReturnsPropertyValue(): void {
        $request = HttpRequest::create(
            'https://example.com',
            'GET',
            [],
            null
        );

        $this->assertSame('https://example.com', $request->url);
    }

    public function testMagicGetThrowsForUnknownProperty(): void {
        $request = HttpRequest::create(
            'https://example.com',
            'GET',
            [],
            null
        );

        $this->expectException(\Exception::class);

        $request->unknown;
    }
}
