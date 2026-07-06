<?php

declare(strict_types=1);

namespace Tests\Unit\Utils\Lists;

use Tests\Support\Utils\Lists\BaseStubExtensionNull;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Base list implementation.
 *
 * Covered:
 * - getList() returns built-in list when extension data is unavailable
 *
 * Notes:
 * - filesystem access is disabled via BaseStubExtensionNull
 * - verifies fallback behaviour of the base implementation
 * - extension loading itself is intentionally not tested here
 *   because it depends on filesystem state
 */
final class BaseTest extends TestCase {
    public function testGetWordsReturnsBuiltInWordsWhenExtensionIsNull(): void {
        $actual = BaseStubExtensionNull::getList();

        $expected = ['fallback-1', 'fallback-2'];

        self::assertSame($expected, $actual);
    }
}
