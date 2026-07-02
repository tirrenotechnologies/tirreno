<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Logger;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Logger.
 *
 * Covered:
 * - Logger::logCronLine()
 *
 * @todo Cover Logger::log() after \Log creation can be replaced
 *       with an injectable log writer.
 *
 * @todo Cover Logger::logSql() after \Log creation can be replaced
 *       with an injectable log writer.
 */
final class LoggerTest extends TestCase {
    /**
     * @dataProvider cronLineProvider
     */
    public function testLogCronLineFormatsAsExpected(string $message, string $cronName, string $expected): void {
        $result = Logger::logCronLine($message, $cronName);

        $this->assertSame($expected, $result);
    }

    public static function cronLineProvider(): array {
        return [
            'simple' => [
                'Started',
                'cronA',
                '[cronA] Started' . PHP_EOL,
            ],
            'message with spaces' => [
                'Hello world',
                'job',
                '[job] Hello world' . PHP_EOL,
            ],
            'message with punctuation' => [
                'Done!',
                'cronB',
                '[cronB] Done!' . PHP_EOL,
            ],
            'empty message' => [
                '',
                'cronC',
                '[cronC] ' . PHP_EOL,
            ],
        ];
    }
}
