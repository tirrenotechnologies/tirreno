<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\SystemMessages;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\SystemMessages.
 *
 * Covered:
 * - SystemMessages::syslogLine()
 *
 * @todo Cover SystemMessages::get() after routes, models, entities,
 *       date range, timezone, storage and current time dependencies
 *       can be replaced in tests.
 *
 * @todo Cover message-building branches after private helper logic is
 *       extracted into deterministic collaborators.
 */
final class SystemMessagesTest extends TestCase {
    public function testSyslogLineBuildsPriTimestampHostAppPidAndMessage(): void {
        $facility = 16;
        $severity = 6;
        $app = 'myapp';
        $msg = "Hello\nworld\r!";

        $line = SystemMessages::syslogLine($facility, $severity, $app, $msg);

        $expectedPri = $facility * 8 + $severity;
        $pid = getmypid();

        // PRI prefix.
        $this->assertStringStartsWith('<' . $expectedPri . '>', $line);

        // App and pid are embedded as "app[PID]:" with fixed host "tirreno".
        $this->assertStringContainsString(' tirreno ' . $app . '[' . $pid . ']: ', $line);

        // Newlines are normalized to spaces.
        $this->assertStringContainsString('Hello world !', $line);

        // No raw CR/LF remains.
        $this->assertStringNotContainsString("\n", $line);
        $this->assertStringNotContainsString("\r", $line);

        // Timestamp format invariant: "M j H:i:s" (month short + space + day (1-2 digits) + time).
        // We don't assert exact time to keep it deterministic.
        $this->assertMatchesRegularExpression(
            '/^<\d+>[A-Z][a-z]{2}\s+\d{1,2}\s+\d{2}:\d{2}:\d{2}\s+tirreno\s+/',
            $line
        );
    }

    public function testSyslogLineCalculatesPriCorrectlyForKnownValues(): void {
        $facility = 0;
        $severity = 0;

        $line = SystemMessages::syslogLine($facility, $severity, 'app', 'x');

        $this->assertStringStartsWith('<0>', $line);

        $facility = 23;
        $severity = 7;

        $line = SystemMessages::syslogLine($facility, $severity, 'app', 'x');

        $expectedPri = $facility * 8 + $severity;
        $this->assertStringStartsWith('<' . $expectedPri . '>', $line);
    }
}
