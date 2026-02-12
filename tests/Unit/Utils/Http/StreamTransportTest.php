<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Entities\HttpRequest;
use Tirreno\Utils\Http\StreamTransport;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Tirreno\Utils\Http\StreamTransport
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
