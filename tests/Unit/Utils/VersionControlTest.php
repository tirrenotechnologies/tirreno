<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\VersionControl;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\VersionControl.
 *
 * Covered:
 * - versionString() (semantic version format: X.Y.Z)
 * - fullVersionString() (prefixed format: vX.Y.Z)
 *
 * Purpose:
 * - guard public version format from accidental changes
 * - ensure constants are composed consistently
 *
 * @todo Refactor:
 * - consider replacing constants with a Version value object
 * - consider single source of truth for version formatting
 */
final class VersionControlTest extends TestCase {
    public function testVersionString(): void {
        $expected = sprintf(
            '%d.%d.%d',
            VersionControl::VERSION_MAJOR,
            VersionControl::VERSION_MINOR,
            VersionControl::VERSION_REVISION
        );

        $actual = VersionControl::versionString();

        $this->assertSame($expected, $actual);
    }

    public function testFullVersionString(): void {
        $expected = sprintf(
            'v%d.%d.%d',
            VersionControl::VERSION_MAJOR,
            VersionControl::VERSION_MINOR,
            VersionControl::VERSION_REVISION
        );

        $actual = VersionControl::fullVersionString();

        $this->assertSame($expected, $actual);
    }
}
