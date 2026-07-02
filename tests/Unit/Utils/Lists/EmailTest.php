<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Lists;

use Tests\Support\Utils\Lists\EmailNoFsStub;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Email list.
 *
 * Covered:
 * - built-in list is returned when extension is disabled
 * - returned value is a non-empty array
 * - all entries are non-empty strings
 * - known built-in signatures are present
 *
 * Notes:
 * - filesystem access is disabled via EmailNoFsStub
 * - tests verify only built-in list behaviour
 */
final class EmailTest extends TestCase {
    public function testGetWordsReturnsBuiltInListWhenExtensionIsNull(): void {
        $words = EmailNoFsStub::getList();

        self::assertIsArray($words);
        self::assertNotEmpty($words);
    }

    public function testGetWordsReturnsOnlyStrings(): void {
        $words = EmailNoFsStub::getList();

        foreach ($words as $word) {
            self::assertIsString($word);
            self::assertNotSame('', $word);
        }
    }

    public function testGetWordsContainsKnownBuiltInEntries(): void {
        $words = EmailNoFsStub::getList();

        self::assertContains('spam', $words);
        self::assertContains('test', $words);
        self::assertContains('dummy', $words);
        self::assertContains('999', $words);
    }
}
