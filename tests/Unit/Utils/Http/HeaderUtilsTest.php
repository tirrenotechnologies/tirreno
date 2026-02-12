<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Http;

use Tirreno\Utils\Http\HeaderUtils;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Tirreno\Utils\Http\HeaderUtils
 */
final class HeaderUtilsTest extends TestCase {
    public function testEnsureHeaderAddsHeaderWhenMissing(): void {
        $headers = [
            'Accept: application/json',
        ];

        $name = 'Content-Type';
        $value = 'application/json';

        $actual = HeaderUtils::ensureHeader($headers, $name, $value);

        $expected = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        $this->assertSame($expected, $actual);
    }

    public function testEnsureHeaderDoesNotDuplicateExistingHeaderCaseInsensitive(): void {
        $headers = [
            'accept: application/json',
            'content-type: text/plain',
        ];

        $name = 'Content-Type';
        $value = 'application/json';

        $actual = HeaderUtils::ensureHeader($headers, $name, $value);

        $this->assertSame($headers, $actual);
    }

    public function testEnsureHeaderDetectsHeaderWithExtraSpaces(): void {
        $headers = [
            '  Content-Type:   text/plain  ',
        ];

        $name = 'Content-Type';
        $value = 'application/json';

        $actual = HeaderUtils::ensureHeader($headers, $name, $value);

        $this->assertSame($headers, $actual);
    }
}
