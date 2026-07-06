<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\ElapsedDate;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\ElapsedDate.
 *
 * Covered:
 * - short(): formats non-null input as "d/m/Y H:i:s"; returns null for null
 * - date(): formats non-null input as "d/m/Y"; returns null for null
 *
 * Partially covered (time-dependent):
 * - long():
 *   - contains "ago."
 *   - contains "and"
 *   - does not return empty string
 *   - includes at least one time unit token for sufficiently old timestamps
 *
 * @todo Refactor ElapsedDate::long() to accept a Clock/current timestamp
 *       so exact elapsed-unit breakdown can be tested deterministically.
 *
 * @todo Define expected output for timestamps with no elapsed units.
 *       Current implementation can return "and ago.".
 *
 * @todo Define exact output grammar for ElapsedDate::long():
 *       "and" placement, pluralization rules and zero-unit behavior.
 */
final class ElapsedDateTest extends TestCase {
    public function testShortReturnsNullForNull(): void {
        $timestampStr = null;

        $actual = ElapsedDate::short($timestampStr);

        $this->assertNull($actual);
    }

    public function testDateReturnsNullForNull(): void {
        $timestampStr = null;

        $actual = ElapsedDate::date($timestampStr);

        $this->assertNull($actual);
    }

    public function testShortFormatsDatetime(): void {
        $timestampStr = '2020-01-02 03:04:05';

        $expected = '02/01/2020 03:04:05';
        $actual = ElapsedDate::short($timestampStr);

        $this->assertSame($expected, $actual);
    }

    public function testDateFormatsDateOnly(): void {
        $timestampStr = '2020-01-02 03:04:05';

        $expected = '02/01/2020';
        $actual = ElapsedDate::date($timestampStr);

        $this->assertSame($expected, $actual);
    }

    public function testLongContainsAgoAndAndIsNotEmptyForOldEnoughTimestamp(): void {
        $secondsInTwoDays = 2 * 24 * 60 * 60;
        $timestamp = time() - $secondsInTwoDays;

        $timestampStr = date('Y-m-d H:i:s', $timestamp);

        $actual = ElapsedDate::long($timestampStr);

        $this->assertNotSame('', $actual);
        $this->assertStringContainsString('ago.', $actual);
        $this->assertStringContainsString('and', $actual);
    }

    public function testLongIncludesAtLeastOneTimeUnitForOldTimestamp(): void {
        $secondsInTwoDays = 2 * 24 * 60 * 60;
        $timestamp = time() - $secondsInTwoDays;

        $timestampStr = date('Y-m-d H:i:s', $timestamp);

        $actual = ElapsedDate::long($timestampStr);

        $hasAnyUnit =
            str_contains($actual, ' year') ||
            str_contains($actual, ' years') ||
            str_contains($actual, ' week') ||
            str_contains($actual, ' weeks') ||
            str_contains($actual, ' day') ||
            str_contains($actual, ' days') ||
            str_contains($actual, ' hour') ||
            str_contains($actual, ' hours') ||
            str_contains($actual, ' minute') ||
            str_contains($actual, ' minutes');

        $this->assertTrue($hasAnyUnit);
    }

    public function testLongIncludesMinuteTokenWhenAtLeastOneMinuteOld(): void {
        $timestamp = time() - 61;

        $timestampStr = date('Y-m-d H:i:s', $timestamp);

        $actual = ElapsedDate::long($timestampStr);

        $this->assertStringContainsString('ago.', $actual);

        $hasMinute =
            str_contains($actual, ' minute') ||
            str_contains($actual, ' minutes');

        $this->assertTrue($hasMinute);
    }
}
