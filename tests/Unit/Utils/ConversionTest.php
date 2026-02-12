<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Conversion;
use Base;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Conversion.
 *
 * These tests focus on behavior and edge cases:
 * - intVal() normalization + validation + fallbacks
 * - intValCheckEmpty() truthy/empty handling
 * - formatKiloValue() threshold + floor rounding
 * - F3 REQUEST/PARAMS helpers (when Base is available)
 */
final class ConversionTest extends TestCase {
    /**
     * @var Base
     */
    private Base $f3;

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = Base::instance();

        $this->f3->clear('REQUEST');
        $this->f3->clear('PARAMS');
    }

    /**
     * @dataProvider intValProvider
     */
    public function testIntVal(mixed $value, ?int $default, ?int $expected): void {
        $result = Conversion::intVal($value, $default);

        $this->assertSame($expected, $result);
    }

    public static function intValProvider(): array {
        $fp = fopen('php://temp', 'rb');

        return [
            'int' => [42, null, 42],
            'zero int' => [0, null, 0],
            'negative int' => [-12, null, -12],
            'string int' => ['42', null, 42],
            'string negative' => ['-42', null, -42],
            'string plus (valid)' => ['+42', null, 42],

            'leading zeros' => ['000123', null, 123],
            'all zeros' => ['000', null, 0],

            'string with spaces (invalid for FILTER_VALIDATE_INT)' => [' 42', null, 42],
            'string trailing spaces (invalid)' => ['42 ', null, 42],
            'string with newline (invalid)' => ["42\n", null, 42],
            'string float (invalid) -> default' => ['42.0', 9, 9],
            'string scientific (invalid) -> default' => ['1e3', 9, 9],

            'empty string -> null default means null' => ['', null, null],
            'empty string -> default 99' => ['', 99, 99],

            'invalid string -> null' => ['abc', null, null],
            'invalid string -> default' => ['abc', 7, 7],

            'float fallback' => [42.7, null, 42],
            'float fallback negative' => [-5.9, null, -5],

            'bool fallback true' => [true, null, 1],
            'bool fallback false' => [false, null, 0],

            'array -> default' => [[1, 2], 5, 5],
            'empty array -> default' => [[], 5, 5],
            'resource -> default' => [$fp, 5, 5],
            'null -> default' => [null, 0, 0],
            'null -> null' => [null, null, null],

            'object __toString numeric' => [new class {
                public function __toString(): string {
                    return '0008';
                }
            }, null, null],
            'object __toString invalid' => [new class {
                public function __toString(): string {
                    return 'abc';
                }
            }, 13, 13],

            'too large int string (platform dependent) -> default' => ['9999999999999999999999999', 77, 77],
        ];
    }

    /**
     * @dataProvider intValCheckEmptyProvider
     */
    public function testIntValCheckEmpty(mixed $value, ?int $default, ?int $expected): void {
        $result = Conversion::intValCheckEmpty($value, $default);

        $this->assertSame($expected, $result);
    }

    public static function intValCheckEmptyProvider(): array {
        return [
            'null -> null' => [null, null, null],
            'empty string -> null' => ['', null, null],
            'string "0" is falsy -> default' => ['0', 10, 10],
            'zero int -> default' => [0, 12, 12],
            'zero float -> default' => [0.0, 13, 13],
            'false -> default' => [false, 14, 14],
            'empty array -> default' => [[], 15, 15],

            'non-empty int' => [42, null, 42],
            'non-empty numeric string' => ['123', null, 123],
            'non-empty numeric string with zeros' => ['05', null, 5],

            'truthy but invalid string -> default' => ['abc', 99, 99],
            'truthy but invalid string -> null default' => ['abc', null, null],
        ];
    }

    /**
     * @dataProvider formatKiloValueProvider
     */
    public function testFormatKiloValue(int $value, string $expected): void {
        $result = Conversion::formatKiloValue($value);

        $this->assertSame($expected, $result);
    }

    public static function formatKiloValueProvider(): array {
        return [
            'below 1k' => [999, '999'],
            'exact 1k' => [1000, '1k'],
            'just above 1k uses floor' => [1001, '1k'],
            '1500 uses floor' => [1500, '1k'],
            '999999 -> 999k' => [999999, '999k'],

            'exact 1M' => [1000000, '1M'],
            'just above 1M uses floor' => [1000001, '1M'],
            '1500000 uses floor' => [1500000, '1M'],
            '2000000 -> 2M' => [2000000, '2M'],
        ];
    }

    public function testRequestAndParamsHelpers(): void {
        $this->f3->set('REQUEST.a', '0007');
        $this->f3->set('REQUEST.b', '');
        $this->f3->set('REQUEST.c', ['x' => 1]);

        $this->f3->set('PARAMS.id', '05');
        $this->f3->set('PARAMS.missing', null);

        $this->assertSame(7, Conversion::getIntRequestParam('a'));
        $this->assertSame('', Conversion::getStringRequestParam('b', false));
        $this->assertNull(Conversion::getStringRequestParam('b', true));
        $this->assertSame(['x' => 1], Conversion::getDictionaryRequestParam('c'));
        $this->assertSame([0 => 1], Conversion::getArrayRequestParam('c'));

        $this->assertSame(5, Conversion::getIntUrlParam('id'));

        // nullable behavior for ints
        $this->assertSame(0, Conversion::getIntRequestParam('missing', false));
        $this->assertNull(Conversion::getIntRequestParam('missing', true));
        $this->assertSame(0, Conversion::getIntUrlParam('missing', false));
        $this->assertNull(Conversion::getIntUrlParam('missing', true));
    }

    public function testGetArrayRequestParamDefaults(): void {
        $this->assertSame([], Conversion::getArrayRequestParam('missing', false));
        $this->assertNull(Conversion::getArrayRequestParam('missing', true));

        $this->f3->set('REQUEST.arr', 'not-an-array');
        $this->assertSame([], Conversion::getArrayRequestParam('arr', false));
        $this->assertNull(Conversion::getArrayRequestParam('arr', true));
    }

    public function testGetDictionaryRequestParamDefaults(): void {
        $this->assertSame([], Conversion::getDictionaryRequestParam('missing', false));
        $this->assertNull(Conversion::getDictionaryRequestParam('missing', true));

        $this->f3->set('REQUEST.arr', 'not-an-array');
        $this->assertSame([], Conversion::getDictionaryRequestParam('arr', false));
        $this->assertNull(Conversion::getDictionaryRequestParam('arr', true));
    }

   /**
     * @dataProvider filterBoolProvider
     */
    public function testFilterBool(mixed $value, ?bool $expected): void {
        $this->assertSame($expected, Conversion::filterBool($value));
    }

    public static function filterBoolProvider(): array {
        return [
            'true string' => ['true', true],
            'false string' => ['false', false],
            '1 string' => ['1', true],
            '0 string' => ['0', false],
            'yes string' => ['yes', true],
            'no string' => ['no', false],
            'on string' => ['on', true],
            'off string' => ['off', false],
            'empty string -> false' => ['', false],
            'random string -> null' => ['maybe', null],
            'int 1' => [1, true],
            'int 0' => [0, false],
            'null -> false' => [null, false],
            'object __toString numeric' => [new class {
                public function __toString(): string {
                    return '0008';
                }
            }, null],
            'array -> null' => [['123'], null],
            'empty array -> null' => [[], null],
        ];
    }

    /**
     * @dataProvider ipProvider
     */
    public function testFilterIpAndType(mixed $value, bool $valid, int|false $type): void {
        $ip = Conversion::filterIp($value);
        $this->assertSame($valid, $ip !== false);

        $this->assertSame($type, Conversion::filterIpGetType($value));
    }

    public static function ipProvider(): array {
        return [
            'valid ipv4' => ['192.168.0.1', true, 4],
            'valid ipv6' => ['2001:db8::1', true, 6],
            'invalid ip' => ['999.1.1.1', false, false],
            'empty' => ['', false, false],
            'null' => [null, false, false],
            'bool' => [true, false, false],
            'object __toString numeric' => [new class {
                public function __toString(): string {
                    return '0008';
                }
            }, false, false],
            'array -> false' => [['123'], false, false],
            'empty array -> false' => [[], false, false],
        ];
    }

    /**
     * @dataProvider emailProvider
     */
    public function testFilterEmail(mixed $value, bool $valid): void {
        $email = Conversion::filterEmail($value);
        $this->assertSame($valid, $email !== false);
    }

    public static function emailProvider(): array {
        return [
            'valid email' => ['a@example.com', true],
            'invalid email' => ['not-an-email', false],
            'empty' => ['', false],
            'null' => [null, false],
            'bool' => [true, false],
            'object __toString numeric' => [new class {
                public function __toString(): string {
                    return '0008';
                }
            }, false],
            'array -> false' => [['123'], false],
            'empty array -> false' => [[], false],
        ];
    }
}
