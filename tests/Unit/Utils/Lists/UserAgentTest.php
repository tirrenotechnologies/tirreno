<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Lists;

use Tests\Support\Utils\Lists\UserAgentNoFsStub;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for UserAgent list.
 *
 * Covered:
 * - built-in list is returned when extension is disabled
 * - returned value is a non-empty array
 * - all entries are non-empty strings
 * - known built-in signatures are present
 */
final class UserAgentTest extends TestCase {
    public function testGetWordsReturnsBuiltInListWhenExtensionIsNull(): void {
        $words = UserAgentNoFsStub::getList();

        self::assertIsArray($words);
        self::assertNotEmpty($words);
    }

    public function testGetWordsReturnsOnlyNonEmptyStrings(): void {
        $words = UserAgentNoFsStub::getList();

        foreach ($words as $word) {
            self::assertIsString($word);
            self::assertNotSame('', $word);
        }
    }

    public function testGetWordsContainsKnownBuiltInEntries(): void {
        $words = UserAgentNoFsStub::getList();

        self::assertContains('select', $words);
        self::assertContains('drop', $words);
        self::assertContains('.exe', $words);
        self::assertContains('/bin', $words);
    }
}
