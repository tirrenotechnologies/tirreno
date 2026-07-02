<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Utils\Http\CurlTransport;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CurlTransport.
 *
 * Covered:
 * - isAvailable() reflects curl extension availability
 *
 * Not covered (recommended to refactor first):
 * - request():
 *   - depends on global curl_* functions
 *   - performs real network operations
 *   - depends on entity factories returned by tirreno('entities')
 *   - cannot be isolated without introducing an HTTP client abstraction
 *
 * @todo Refactor:
 * - extract HttpClientInterface
 * - wrap curl_* calls behind a transport adapter
 * - inject HttpResponse factory instead of using tirreno('entities')
 * - after that, request() can be unit-tested without network access
 */
final class CurlTransportTest extends TestCase {
    public function testIsAvailableMatchesFunctionExists(): void {
        $transport = new CurlTransport();

        $expected = function_exists('curl_init');
        $actual = $transport->isAvailable();

        $this->assertSame($expected, $actual);
    }
}
