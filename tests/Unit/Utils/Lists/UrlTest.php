<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Lists;

use Tests\Support\Utils\Lists\UrlNoFsStub;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Url list.
 *
 * Covered:
 * - built-in list is returned when extension is disabled
 * - returned value is a non-empty array
 * - all entries are non-empty strings
 * - known built-in signatures are present
 */
final class UrlTest extends TestCase {
    public function testGetWordsReturnsBuiltInListWhenExtensionIsNull(): void {
        $words = UrlNoFsStub::getList();

        self::assertIsArray($words);
        self::assertNotEmpty($words);
    }

    public function testGetWordsReturnsOnlyNonEmptyStrings(): void {
        $words = UrlNoFsStub::getList();

        foreach ($words as $word) {
            self::assertIsString($word);
            self::assertNotSame('', $word);
        }
    }

    public function testGetWordsContainsKnownBuiltInEntries(): void {
        $words = UrlNoFsStub::getList();

        self::assertContains('../', $words);
        self::assertContains('.env', $words);
        self::assertContains('wp-config', $words);
        self::assertContains('<script>', $words);
    }
}
