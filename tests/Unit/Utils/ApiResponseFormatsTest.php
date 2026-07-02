<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\ApiResponseFormats;
use PHPUnit\Framework\TestCase;

final class ApiResponseFormatsTest extends TestCase {
    public function testMatchResponseReturnsTrueWhenAllKeysExistEvenIfValuesAreNull(): void {
        $format = ['a', 'b', 'c'];

        $response = [
            'a' => null,
            'b' => null,
            'c' => null,
        ];

        $result = ApiResponseFormats::matchResponse($response, $format);

        self::assertTrue($result);
    }

    public function testMatchResponseReturnsFalseWhenAnyKeyIsMissing(): void {
        $format = ['a', 'b', 'c'];

        $response = [
            'a' => 1,
            'b' => 2,
            // c missing
        ];

        $result = ApiResponseFormats::matchResponse($response, $format);

        self::assertFalse($result);
    }

    public function testMatchResponseIgnoresExtraKeys(): void {
        $format = ['a', 'b'];

        $response = [
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ];

        self::assertTrue(
            ApiResponseFormats::matchResponse($response, $format)
        );
    }
}
