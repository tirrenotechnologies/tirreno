<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Entities\HttpRequest;
use Tirreno\Utils\Http\StreamTransport;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for StreamTransport.
 *
 * Covered:
 * - isAvailable() reflects file_get_contents availability
 * - request() returns failure response when stream reading fails
 *
 * Not covered:
 * - successful HTTP response handling
 * - HTTP status extraction from real response headers
 * - SSL option behavior
 * - request body/header/method context options
 *
 * Notes:
 * - failure branch is tested with a missing local file URL
 * - no real network request is performed
 *
 * @todo Refactor StreamTransport so safeFileGetContents() and
 *       extractHttpStatus() can be tested without relying on global
 *       file_get_contents(), $http_response_header and stream context state.
 */
final class StreamTransportTest extends TestCase {
    protected function tearDown(): void {
        @restore_error_handler();

        parent::tearDown();
    }

    public function testIsAvailableMatchesFunctionExists(): void {
        $transport = new StreamTransport();

        $expected = function_exists('file_get_contents');
        $actual = $transport->isAvailable();

        $this->assertSame($expected, $actual);
    }

    public function testRequestReturnsFailureForMissingFile(): void {
        $transport = new StreamTransport();

        if (!$transport->isAvailable()) {
            $this->markTestSkipped('file_get_contents is not available in this environment.');
        }

        $path = 'file:///this/path/does/not/exist_' . bin2hex(random_bytes(8));

        $request = new HttpRequest(
            $path,
            'GET',
            [],
            null,
            1,
            1,
            true
        );

        $response = $transport->request($request);

        $this->assertFalse($response->ok());
        $this->assertSame('stream_request_failed', $response->error());
    }
}
