<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Lists;

use Tests\Support\Utils\Lists\FileExtensionsStubWithExtension;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for FileExtensions.
 *
 * Covered:
 * - getList() returns extension data provided by getExtension()
 * - getKeys() returns top-level keys from the extension map
 * - getValues() returns values for an existing key
 * - getValues() returns an empty array for an unknown key
 *
 * Notes:
 * - filesystem access is disabled via FileExtensionsStubWithExtension
 * - tests verify only public API behaviour
 */
final class FileExtensionsTest extends TestCase {
    public function testGetWordsReturnsExtensionMapWhenExtensionProvided(): void {
        $actual = FileExtensionsStubWithExtension::getList();

        $expected = [
            'images' => ['jpg', 'png'],
            'docs' => ['pdf'],
        ];

        self::assertSame($expected, $actual);
    }

    public function testGetKeysReturnsArrayKeysFromWords(): void {
        $actual = FileExtensionsStubWithExtension::getKeys();

        $expected = ['images', 'docs'];

        self::assertSame($expected, $actual);
    }

    public function testGetValuesReturnsValueForExistingKeyAndEmptyForMissing(): void {
        $actualImages = FileExtensionsStubWithExtension::getValues('images');
        $expectedImages = ['jpg', 'png'];

        self::assertSame($expectedImages, $actualImages);

        $actualMissing = FileExtensionsStubWithExtension::getValues('missing');
        $expectedMissing = [];

        self::assertSame($expectedMissing, $actualMissing);
    }
}
