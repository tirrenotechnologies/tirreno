<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\DateRange;
use Base;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\DateRange.
 *
 * Covered:
 * - DateRange::inIntervalTillNow()
 * - DateRange::isQueueTimeouted()
 * - DateRange::getDatesRangeByGivenDates()
 * - DateRange::getDatesRangeFromRequest()
 * - DateRange::getLatestNDatesRangeFromRequest()
 * - DateRange::getResolutionFromRequest()
 */
final class DateRangeTest extends TestCase {
    private Base $f3;

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = Base::instance();

        $this->clearRequestState();

        tirreno('request')->resetPayloadCache();
    }

    protected function tearDown(): void {
        $this->clearRequestState();

        tirreno('request')->resetPayloadCache();

        parent::tearDown();
    }

    /**
     * @dataProvider inIntervalTillNowProvider
     */
    public function testInIntervalTillNow(?string $time, int $interval, ?bool $expected): void {
        $result = DateRange::inIntervalTillNow($time, $interval);

        $this->assertSame($expected, $result);
    }

    public static function inIntervalTillNowProvider(): array {
        $now = time();

        return [
            'null time -> null' => [
                'time' => null,
                'interval' => 60,
                'expected' => null,
            ],
            'within interval' => [
                'time' => gmdate('Y-m-d H:i:s', $now - 10),
                'interval' => 60,
                'expected' => true,
            ],
            'outside interval' => [
                'time' => gmdate('Y-m-d H:i:s', $now - 120),
                'interval' => 60,
                'expected' => false,
            ],
            'future still counts by abs diff' => [
                'time' => gmdate('Y-m-d H:i:s', $now + 30),
                'interval' => 60,
                'expected' => true,
            ],
            'zero interval always false for non-null time' => [
                'time' => gmdate('Y-m-d H:i:s', $now - 1),
                'interval' => 0,
                'expected' => false,
            ],
        ];
    }

    public function testIsQueueTimeoutedReturnsFalseForRecentUpdate(): void {
        $updated = gmdate('Y-m-d H:i:s');

        $result = DateRange::isQueueTimeouted($updated);

        $this->assertFalse($result);
    }

    public function testIsQueueTimeoutedReturnsTrueForOldUpdate(): void {
        $updated = gmdate('Y-m-d H:i:s', time() - 999999);

        $result = DateRange::isQueueTimeouted($updated);

        $this->assertTrue($result);
    }

    /**
     * @dataProvider getDatesRangeByGivenDatesProvider
     */
    public function testGetDatesRangeByGivenDates(
        string $startDate,
        string $endDate,
        int $offset,
        array $expected
    ): void {
        $result = DateRange::getDatesRangeByGivenDates($startDate, $endDate, $offset);

        $this->assertSame($expected, $result);
    }

    public static function getDatesRangeByGivenDatesProvider(): array {
        return [
            'zero offset' => [
                'startDate' => '2024-01-01 00:00:00',
                'endDate' => '2024-01-31 23:59:59',
                'offset' => 0,
                'expected' => [
                    'endDate' => '2024-01-31 23:59:59',
                    'startDate' => '2024-01-01 00:00:00',
                ],
            ],
            'positive offset 1h' => [
                'startDate' => '2024-01-01 12:00:00',
                'endDate' => '2024-01-02 12:00:00',
                'offset' => 3600,
                'expected' => [
                    'endDate' => '2024-01-02 13:00:00',
                    'startDate' => '2024-01-01 13:00:00',
                ],
            ],
            'negative offset 1h' => [
                'startDate' => '2024-01-01 12:00:00',
                'endDate' => '2024-01-02 12:00:00',
                'offset' => -3600,
                'expected' => [
                    'endDate' => '2024-01-02 11:00:00',
                    'startDate' => '2024-01-01 11:00:00',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getResolutionFromRequestProvider
     */
    public function testGetResolutionFromRequest(?string $requestValue, string $expected): void {
        if ($requestValue === null) {
            $this->f3->clear('GET.resolution');
        } else {
            $this->f3->set('GET.resolution', $requestValue);
        }

        tirreno('request')->resetPayloadCache();

        $result = DateRange::getResolutionFromRequest();

        $this->assertSame($expected, $result);
    }

    public static function getResolutionFromRequestProvider(): array {
        return [
            'missing request -> day' => [
                'requestValue' => null,
                'expected' => 'day',
            ],
            'valid request hour -> hour' => [
                'requestValue' => 'hour',
                'expected' => 'hour',
            ],
            'valid request minute -> minute' => [
                'requestValue' => 'minute',
                'expected' => 'minute',
            ],
            'invalid request -> day' => [
                'requestValue' => 'week',
                'expected' => 'day',
            ],
        ];
    }

    /**
     * @dataProvider getDatesRangeFromRequestProvider
     */
    public function testGetDatesRangeFromRequest(
        ?string $dateFrom,
        ?string $dateTo,
        ?int $keepDates,
        int $offset,
        ?array $expectedDates,
        ?string $expectedSessionStart,
        ?string $expectedSessionEnd
    ): void {
        if ($dateFrom !== null) {
            $this->f3->set('GET.dateFrom', $dateFrom);
        }

        if ($dateTo !== null) {
            $this->f3->set('GET.dateTo', $dateTo);
        }

        if ($keepDates !== null) {
            $this->f3->set('GET.keepDates', $keepDates);
        }

        tirreno('request')->resetPayloadCache();

        $result = DateRange::getDatesRangeFromRequest($offset);

        $this->assertSame($expectedDates, $result);
        $this->assertSame($expectedSessionStart, $this->f3->get('SESSION.filterStartDate'));
        $this->assertSame($expectedSessionEnd, $this->f3->get('SESSION.filterEndDate'));
    }

    public static function getDatesRangeFromRequestProvider(): array {
        return [
            'missing both dates -> null, no session set' => [
                'dateFrom' => null,
                'dateTo' => null,
                'keepDates' => null,
                'offset' => 0,
                'expectedDates' => null,
                'expectedSessionStart' => null,
                'expectedSessionEnd' => null,
            ],
            'dates provided, keepDates=1 -> session set' => [
                'dateFrom' => '2024-01-01 12:00:00',
                'dateTo' => '2024-01-02 12:00:00',
                'keepDates' => 1,
                'offset' => 3600,
                'expectedDates' => [
                    'endDate' => '2024-01-02 13:00:00',
                    'startDate' => '2024-01-01 13:00:00',
                ],
                'expectedSessionStart' => '2024-01-01 13:00:00',
                'expectedSessionEnd' => '2024-01-02 13:00:00',
            ],
            'dates provided, keepDates=0 -> session cleared to nulls' => [
                'dateFrom' => '2024-01-01 12:00:00',
                'dateTo' => '2024-01-02 12:00:00',
                'keepDates' => 0,
                'offset' => 0,
                'expectedDates' => [
                    'endDate' => '2024-01-02 12:00:00',
                    'startDate' => '2024-01-01 12:00:00',
                ],
                'expectedSessionStart' => null,
                'expectedSessionEnd' => null,
            ],
        ];
    }

    public function testGetLatestNDatesRangeFromRequestReturnsValidFormat(): void {
        $days = 7;

        $result = DateRange::getLatestNDatesRangeFromRequest($days, 0);

        $this->assertArrayHasKey('startDate', $result);
        $this->assertArrayHasKey('endDate', $result);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} 00:00:01$/', $result['startDate']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} 23:59:59$/', $result['endDate']);

        $startTs = strtotime($result['startDate']);
        $endTs = strtotime($result['endDate']);

        $this->assertNotFalse($startTs);
        $this->assertNotFalse($endTs);
        $this->assertLessThan($endTs, $startTs, 'startDate must be earlier than endDate');

        $startDay = new \DateTimeImmutable(substr($result['startDate'], 0, 10));
        $endDay = new \DateTimeImmutable(substr($result['endDate'], 0, 10));

        $diffDays = $endDay->diff($startDay)->days;

        $this->assertSame($days, $diffDays);
    }

    private function clearRequestState(): void {
        $keys = [
            'REQUEST',
            'SESSION',
            'GET',
            'POST',
            'BODY',
            'HEADERS',
            'PARAMS',
        ];

        foreach ($keys as $key) {
            $this->f3->clear($key);
        }
    }
}
