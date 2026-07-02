<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Timezones;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Timezones.
 *
 * Covered:
 * - Timezones::getTimezone()
 * - Timezones::getUtcTimezone()
 * - Timezones::localizeTimestamp()
 * - Timezones::addOffset()
 * - Timezones::getSecondsSinceMonday()
 * - Timezones::getTodayRange()
 *
 * Partially covered:
 * - range helpers are covered only through stable invariants because they use time().
 *
 * @todo Cover active-operator timezone methods after current operator resolving
 *       can be replaced in tests.
 *
 * @todo Cover timezonesList() after available timezone catalog and current time
 *       can be replaced in tests.
 *
 * @todo Add deterministic coverage for all range helpers after extracting Clock.
 */
final class TimezonesTest extends TestCase {
    public function testGetUtcTimezoneReturnsUtc(): void {
        $timezone = Timezones::getUtcTimezone();

        $this->assertSame('UTC', $timezone->getName());
    }

    public function testGetTimezoneReturnsDefaultWhenInvalid(): void {
        $timezone = Timezones::getTimezone('Not/A_Timezone', 'UTC');

        $this->assertSame('UTC', $timezone->getName());
    }

    public function testGetTimezoneReturnsProvidedWhenValid(): void {
        $timezone = Timezones::getTimezone('Europe/Kyiv', 'UTC');

        $this->assertSame('Europe/Kyiv', $timezone->getName());
    }

    public function testLocalizeTimestampConvertsFromUtcToFixedTimezoneWithoutMilliseconds(): void {
        $utc = new \DateTimeZone('UTC');
        $until = new \DateTimeZone('Europe/Kyiv');

        $input = '2020-01-01 00:00:00';

        $result = Timezones::localizeTimestamp($input, $utc, $until, false);

        $date = \DateTime::createFromFormat(Timezones::FORMAT, $input, $utc);
        $date->setTimezone($until);

        $expected = $date->format(Timezones::FORMAT);

        $this->assertSame($expected, $result);
    }

    public function testLocalizeTimestampKeepsMicrosecondsWhenRequested(): void {
        $utc = new \DateTimeZone('UTC');
        $until = new \DateTimeZone('UTC');

        $input = '2020-01-01 00:00:00.123456';

        $result = Timezones::localizeTimestamp($input, $utc, $until, true);

        $this->assertSame($input, $result);
    }

    public function testAddOffsetAddsSecondsWithoutMilliseconds(): void {
        $input = '2020-01-01 00:00:00';

        $result = Timezones::addOffset($input, 60, false);

        $this->assertSame('2020-01-01 00:01:00', $result);
    }

    public function testAddOffsetPreservesMillisecondSuffixWhenPresent(): void {
        $input = '2020-01-01 00:00:00.123456';

        $result = Timezones::addOffset($input, 1, true);

        $this->assertSame('2020-01-01 00:00:01.123456', $result);
    }

    public function testAddOffsetFallsBackWhenMillisecondsRequestedButMissingInInput(): void {
        $input = '2020-01-01 00:00:00';

        $result = Timezones::addOffset($input, 1, true);

        $this->assertSame('2020-01-01 00:00:01', $result);
    }

    /**
     * @dataProvider secondsSinceMondayProvider
     */
    public function testGetSecondsSinceMonday(string $timestamp, int $expected): void {
        $result = Timezones::getSecondsSinceMonday($timestamp);

        $this->assertSame($expected, $result);
    }

    public static function secondsSinceMondayProvider(): array {
        return [
            'monday midnight' => [
                'timestamp' => '2024-01-01 00:00:00',
                'expected' => 0,
            ],
            'monday one hour' => [
                'timestamp' => '2024-01-01 01:00:00',
                'expected' => 3600,
            ],
            'tuesday midnight' => [
                'timestamp' => '2024-01-02 00:00:00',
                'expected' => 86400,
            ],
            'sunday last second' => [
                'timestamp' => '2024-01-07 23:59:59',
                'expected' => 604799,
            ],
        ];
    }

    public function testCurDayRangeHasValidFormatAndOrdering(): void {
        $range = Timezones::getTodayRange(0);

        $startDate = (string) $range['startDate'];
        $endDate = (string) $range['endDate'];

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $startDate);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $endDate);

        $this->assertStringEndsWith('00:00:00', $startDate);

        $timezone = new \DateTimeZone(date_default_timezone_get());

        $start = \DateTimeImmutable::createFromFormat(Timezones::FORMAT, $startDate, $timezone);
        $end = \DateTimeImmutable::createFromFormat(Timezones::FORMAT, $endDate, $timezone);

        $this->assertInstanceOf(\DateTimeImmutable::class, $start);
        $this->assertInstanceOf(\DateTimeImmutable::class, $end);

        $this->assertLessThanOrEqual(
            $end->getTimestamp(),
            $start->getTimestamp()
        );
    }
}
