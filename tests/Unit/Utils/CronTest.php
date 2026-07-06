<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Cron;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Cron.
 *
 * Covered:
 * - Cron::getHashes()
 * - Cron::printLogs()
 * - Cron::parseTimestamp()
 * - Cron::parseExpression()
 *
 * @todo Cover Cron::checkTimezone() after current time/timezone resolving
 *       can be injected or otherwise controlled in tests.
 *
 * @todo Cover Cron::sendBlacklistReportPostRequest() after network client
 *       can be replaced in tests.
 *
 * @todo Cover Cron::sendUnreviewedItemsReminderEmail() after Audit, storage,
 *       variables and mailer dependencies can be replaced in tests.
 */
final class CronTest extends TestCase {
    /**
     * @dataProvider getHashesProvider
     */
    public function testGetHashes(
        array $items,
        string $userEmail,
        array $expectedTypes,
        array $expectedRawValues
    ): void {
        $result = Cron::getHashes($items, $userEmail);

        $this->assertCount(count($items), $result);

        $expectedUserHash = hash('sha256', $userEmail);

        $iters = count($result);

        for ($i = 0; $i < $iters; ++$i) {
            $row = $result[$i];

            $this->assertSame($expectedTypes[$i], $row['type']);
            $this->assertSame(hash('sha256', $expectedRawValues[$i]), $row['value']);
            $this->assertSame($expectedUserHash, $row['id']);
        }

        if (count($items) > 1) {
            $firstId = $result[0]['id'];

            for ($i = 1; $i < count($items); ++$i) {
                $this->assertSame($firstId, $result[$i]['id']);
            }
        }
    }

    public static function getHashesProvider(): array {
        return [
            'empty items' => [
                'items' => [],
                'userEmail' => 'user@example.com',
                'expectedTypes' => [],
                'expectedRawValues' => [],
            ],
            'single item' => [
                'items' => [
                    ['type' => 'email', 'value' => 'test@example.com'],
                ],
                'userEmail' => 'user@example.com',
                'expectedTypes' => ['email'],
                'expectedRawValues' => ['test@example.com'],
            ],
            'multiple items' => [
                'items' => [
                    ['type' => 'email', 'value' => 'test@example.com'],
                    ['type' => 'ip', 'value' => '192.168.1.1'],
                    ['type' => 'phone', 'value' => '+1234567890'],
                ],
                'userEmail' => 'user@example.com',
                'expectedTypes' => ['email', 'ip', 'phone'],
                'expectedRawValues' => ['test@example.com', '192.168.1.1', '+1234567890'],
            ],
            'same value produces same hash' => [
                'items' => [
                    ['type' => 'email', 'value' => 'same@example.com'],
                    ['type' => 'email', 'value' => 'same@example.com'],
                ],
                'userEmail' => 'user@example.com',
                'expectedTypes' => ['email', 'email'],
                'expectedRawValues' => ['same@example.com', 'same@example.com'],
            ],
        ];
    }

    /**
     * @dataProvider printLogsProvider
     */
    public function testPrintLogs(array $logs, string $expectedOutput): void {
        ob_start();
        Cron::printLogs($logs);
        $output = ob_get_clean();

        $this->assertSame($expectedOutput, $output);
    }

    public static function printLogsProvider(): array {
        return [
            'empty array' => [
                'logs' => [],
                'expectedOutput' => '',
            ],
            'single log' => [
                'logs' => ['Test log message'],
                'expectedOutput' => 'Test log message',
            ],
            'multiple logs concatenated' => [
                'logs' => ['Log 1', 'Log 2', 'Log 3'],
                'expectedOutput' => 'Log 1Log 2Log 3',
            ],
            'preserves new lines' => [
                'logs' => ['Line1' . PHP_EOL, 'Line2' . PHP_EOL],
                'expectedOutput' => 'Line1' . PHP_EOL . 'Line2' . PHP_EOL,
            ],
        ];
    }

    public function testParseTimestamp(): void {
        $time = new \DateTime('2026-06-16 09:05:00');

        $actual = Cron::parseTimestamp($time);

        $this->assertSame([5, 9, 16, 6, 2], $actual);
    }

    /**
     * @dataProvider parseExpressionValidProvider
     */
    public function testParseExpressionReturnsPartsForValidExpression(
        string $expression,
        array $expected
    ): void {
        $actual = Cron::parseExpression($expression);

        $this->assertSame($expected, $actual);
    }

    public static function parseExpressionValidProvider(): array {
        return [
            'every minute' => [
                'expression' => '* * * * *',
                'expected' => [
                    range(0, 59),
                    range(0, 23),
                    range(1, 31),
                    range(1, 12),
                    range(0, 6),
                ],
            ],
            'exact values' => [
                'expression' => '5 9 16 6 2',
                'expected' => [
                    [5],
                    [9],
                    [16],
                    [6],
                    [2],
                ],
            ],
            'range' => [
                'expression' => '0-2 9 1 1 1',
                'expected' => [
                    [0, 1, 2],
                    [9],
                    [1],
                    [1],
                    [1],
                ],
            ],
            'step' => [
                'expression' => '*/15 * * * *',
                'expected' => [
                    [0, 15, 30, 45],
                    range(0, 23),
                    range(1, 31),
                    range(1, 12),
                    range(0, 6),
                ],
            ],
            'list' => [
                'expression' => '1,3,5 9 * * *',
                'expected' => [
                    [1, 3, 5],
                    [9],
                    range(1, 31),
                    range(1, 12),
                    range(0, 6),
                ],
            ],
        ];
    }

    /**
     * @dataProvider parseExpressionInvalidProvider
     */
    public function testParseExpressionReturnsFalseForInvalidExpression(string $expression): void {
        $actual = Cron::parseExpression($expression);

        $this->assertFalse($actual);
    }

    public static function parseExpressionInvalidProvider(): array {
        return [
            'too few parts' => [
                'expression' => '* * * *',
            ],
            'too many parts' => [
                'expression' => '* * * * * *',
            ],
            'invalid token' => [
                'expression' => 'abc * * * *',
            ],
            'minute out of range' => [
                'expression' => '60 * * * *',
            ],
            'hour out of range' => [
                'expression' => '* 24 * * *',
            ],
            'day of month out of range' => [
                'expression' => '* * 32 * *',
            ],
            'month out of range' => [
                'expression' => '* * * 13 *',
            ],
            'day of week out of range' => [
                'expression' => '* * * * 7',
            ],
            'range start greater than end' => [
                'expression' => '10-5 * * * *',
            ],
            'zero step' => [
                'expression' => '*/0 * * * *',
            ],
        ];
    }
}
