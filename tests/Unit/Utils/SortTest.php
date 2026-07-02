<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Sort;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Sort.
 *
 * Covered:
 * - Sort::cmpTimestamp()
 * - Sort::cmpScore()
 * - Sort::cmpSetScore()
 * - Sort::cmpSetUid()
 * - Sort::cmpRule()
 *
 * Notes:
 * - tests assert comparator sign (-/0/+) rather than exact values for robustness
 * - cmpTimestamp returns a numeric difference, not a spaceship result
 */
final class SortTest extends TestCase {
    /**
     * @dataProvider cmpTimestampProvider
     */
    public function testCmpTimestampReturnsExpectedSign(
        array $left,
        array $right,
        int $expectedSign
    ): void {
        $result = Sort::cmpTimestamp($left, $right);

        $this->assertSame($expectedSign, $this->sign($result));
    }

    public static function cmpTimestampProvider(): array {
        return [
            'left smaller ts => negative' => [
                'left' => ['ts' => 10],
                'right' => ['ts' => 20],
                'expectedSign' => -1,
            ],
            'equal ts => zero' => [
                'left' => ['ts' => 10],
                'right' => ['ts' => 10],
                'expectedSign' => 0,
            ],
            'left larger ts => positive' => [
                'left' => ['ts' => 30],
                'right' => ['ts' => 20],
                'expectedSign' => 1,
            ],
        ];
    }

    /**
     * @dataProvider cmpScoreProvider
     */
    public function testCmpScoreReturnsExpectedSign(
        array $left,
        array $right,
        int $expectedSign
    ): void {
        $result = Sort::cmpScore($left, $right);

        $this->assertSame($expectedSign, $this->sign($result));
    }

    public static function cmpScoreProvider(): array {
        return [
            'left higher score => negative' => [
                'left' => ['score' => 100],
                'right' => ['score' => 50],
                'expectedSign' => -1,
            ],
            'equal score => zero' => [
                'left' => ['score' => 10],
                'right' => ['score' => 10],
                'expectedSign' => 0,
            ],
            'left lower score => positive' => [
                'left' => ['score' => 5],
                'right' => ['score' => 10],
                'expectedSign' => 1,
            ],
        ];
    }

    /**
     * @dataProvider cmpSetScoreProvider
     */
    public function testCmpSetScoreReturnsExpectedSign(
        array $left,
        array $right,
        int $expectedSign
    ): void {
        $result = Sort::cmpSetScore($left, $right);

        $this->assertSame($expectedSign, $this->sign($result));
    }

    public static function cmpSetScoreProvider(): array {
        return [
            'left higher set => negative' => [
                'left' => ['set' => 2, 'score' => 10],
                'right' => ['set' => 1, 'score' => 100],
                'expectedSign' => -1,
            ],
            'left lower set => positive' => [
                'left' => ['set' => 1, 'score' => 100],
                'right' => ['set' => 2, 'score' => 10],
                'expectedSign' => 1,
            ],
            'same set left higher score => negative' => [
                'left' => ['set' => 1, 'score' => 100],
                'right' => ['set' => 1, 'score' => 50],
                'expectedSign' => -1,
            ],
            'same set left lower score => positive' => [
                'left' => ['set' => 1, 'score' => 50],
                'right' => ['set' => 1, 'score' => 100],
                'expectedSign' => 1,
            ],
            'same set same score => zero' => [
                'left' => ['set' => 1, 'score' => 50],
                'right' => ['set' => 1, 'score' => 50],
                'expectedSign' => 0,
            ],
        ];
    }

    /**
     * @dataProvider cmpSetUidProvider
     */
    public function testCmpSetUidReturnsExpectedSign(
        array $left,
        array $right,
        int $expectedSign
    ): void {
        $result = Sort::cmpSetUid($left, $right);

        $this->assertSame($expectedSign, $this->sign($result));
    }

    public static function cmpSetUidProvider(): array {
        return [
            'left higher set => negative' => [
                'left' => ['set' => 2, 'uid' => 'A01'],
                'right' => ['set' => 1, 'uid' => 'Z99'],
                'expectedSign' => -1,
            ],
            'left lower set => positive' => [
                'left' => ['set' => 1, 'uid' => 'Z99'],
                'right' => ['set' => 2, 'uid' => 'A01'],
                'expectedSign' => 1,
            ],
            'same set left higher uid => negative' => [
                'left' => ['set' => 1, 'uid' => 'B01'],
                'right' => ['set' => 1, 'uid' => 'A01'],
                'expectedSign' => -1,
            ],
            'same set left lower uid => positive' => [
                'left' => ['set' => 1, 'uid' => 'A01'],
                'right' => ['set' => 1, 'uid' => 'B01'],
                'expectedSign' => 1,
            ],
            'same set same uid => zero' => [
                'left' => ['set' => 1, 'uid' => 'A01'],
                'right' => ['set' => 1, 'uid' => 'A01'],
                'expectedSign' => 0,
            ],
        ];
    }

    public function testCmpRuleSortsByValidatedDescFirst(): void {
        $left = [
            'validated' => 0,
            'missing' => false,
            'uid' => 'A01',
        ];

        $right = [
            'validated' => 1,
            'missing' => false,
            'uid' => 'A01',
        ];

        $result = Sort::cmpRule($left, $right);

        $this->assertSame(1, $this->sign($result), 'validated=1 must come before validated=0');
    }

    public function testCmpRuleSortsByMissingFalseBeforeTrueWhenValidatedEqual(): void {
        $left = [
            'validated' => 1,
            'missing' => false,
            'uid' => 'A01',
        ];

        $right = [
            'validated' => 1,
            'missing' => true,
            'uid' => 'A01',
        ];

        $result = Sort::cmpRule($left, $right);

        $this->assertSame(-1, $this->sign($result), 'missing=false must come before missing=true');
    }

    public function testCmpRuleSortsByUidAscWhenValidatedAndMissingEqual(): void {
        $left = [
            'validated' => 1,
            'missing' => false,
            'uid' => 'A01',
        ];

        $right = [
            'validated' => 1,
            'missing' => false,
            'uid' => 'B01',
        ];

        $result = Sort::cmpRule($left, $right);

        $this->assertSame(-1, $this->sign($result), 'uid must be sorted ascending');
    }

    public function testCmpRuleReturnsZeroWhenAllKeysEqual(): void {
        $left = [
            'validated' => 1,
            'missing' => false,
            'uid' => 'A01',
        ];

        $right = [
            'validated' => 1,
            'missing' => false,
            'uid' => 'A01',
        ];

        $result = Sort::cmpRule($left, $right);

        $this->assertSame(0, $result);
    }

    private function sign(int $value): int {
        if ($value === 0) {
            return 0;
        }

        return $value < 0 ? -1 : 1;
    }
}
