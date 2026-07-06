<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Entities\HttpResponse;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for HttpResponse.
 *
 * Covered:
 * - success() factory
 * - failure() factory
 * - create() factory
 * - JSON body decoding
 * - invalid JSON fallback to empty array
 * - null body preservation
 * - magic property access through __get()
 * - unknown property exception
 *
 * Notes:
 * - HttpResponse is currently a simple DTO/value object
 * - tests verify response data is preserved and normalized as expected
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

    public function testCreateResponse(): void {
        $response = HttpResponse::create(
            true,
            201,
            '{"created": true}',
            null,
            ['HTTP/1.1 201 Created']
        );

        $this->assertTrue($response->ok());
        $this->assertSame(201, $response->code());
        $this->assertSame(['created' => true], $response->body());
        $this->assertNull($response->error());
        $this->assertSame(['HTTP/1.1 201 Created'], $response->headers());
    }

    public function testInvalidJsonBodyBecomesEmptyArray(): void {
        $response = HttpResponse::success(
            200,
            'not-json',
            []
        );

        $this->assertSame([], $response->body());
    }

    public function testNullBodyIsPreserved(): void {
        $response = HttpResponse::create(
            true,
            204,
            null,
            null,
            []
        );

        $this->assertNull($response->body());
    }

    public function testMagicGetReturnsPropertyValue(): void {
        $response = HttpResponse::success(
            200,
            '{"ok": true}',
            []
        );

        $this->assertTrue($response->ok);
        $this->assertSame(200, $response->code);
        $this->assertSame(['ok' => true], $response->body);
    }

    public function testMagicGetThrowsForUnknownProperty(): void {
        $response = HttpResponse::success(
            200,
            '{"ok": true}',
            []
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown property unknown');

        $response->unknown;
    }
}
