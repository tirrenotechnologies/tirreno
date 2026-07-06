<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Assets\RulesClasses;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Assets\RulesClasses.
 *
 * Covered:
 * - RulesClasses::getRuleClass()
 * - RulesClasses::getRuleTypeByUid()
 *
 * @todo Cover RulesClasses::getRulesClasses() after filesystem scanning,
 *       class loading and reflection can be isolated.
 *
 * @todo Cover RulesClasses::getSingleRuleObject() after rule discovery,
 *       class validation and object creation can be isolated.
 *
 * @todo Cover RulesClasses::getAllRulesObjects() after getRulesClasses()
 *       and rule instantiation can be isolated.
 */
final class RulesClassesTest extends TestCase {
    /**
     * @dataProvider ruleClassProvider
     */
    public function testGetRuleClassReturnsExpectedClass(?int $value, bool $broken, string $expected): void {
        $result = RulesClasses::getRuleClass($value, $broken);

        $this->assertSame($expected, $result);
    }

    public static function ruleClassProvider(): array {
        return [
            'broken overrides everything' => [
                'value' => 20,
                'broken' => true,
                'expected' => 'broken',
            ],
            'null value uses default 0 => none' => [
                'value' => null,
                'broken' => false,
                'expected' => 'none',
            ],
            'explicit 0 => none' => [
                'value' => 0,
                'broken' => false,
                'expected' => 'none',
            ],
            '-20 => positive' => [
                'value' => -20,
                'broken' => false,
                'expected' => 'positive',
            ],
            '10 => medium' => [
                'value' => 10,
                'broken' => false,
                'expected' => 'medium',
            ],
            '20 => high' => [
                'value' => 20,
                'broken' => false,
                'expected' => 'high',
            ],
            '70 => extreme' => [
                'value' => 70,
                'broken' => false,
                'expected' => 'extreme',
            ],
            'unknown value => none' => [
                'value' => 999,
                'broken' => false,
                'expected' => 'none',
            ],
        ];
    }

    /**
     * @dataProvider ruleTypeProvider
     */
    public function testGetRuleTypeByUidReturnsExpectedType(string $uid, string $expected): void {
        $result = RulesClasses::getRuleTypeByUid($uid);

        $this->assertSame($expected, $result);
    }

    public static function ruleTypeProvider(): array {
        return [
            'A => Account takeover' => [
                'uid' => 'A01',
                'expected' => 'Account takeover',
            ],
            'B => Behaviour' => [
                'uid' => 'B12',
                'expected' => 'Behaviour',
            ],
            'C => Country' => [
                'uid' => 'C999',
                'expected' => 'Country',
            ],
            'D => Device' => [
                'uid' => 'D01',
                'expected' => 'Device',
            ],
            'E => Email' => [
                'uid' => 'E02',
                'expected' => 'Email',
            ],
            'I => IP' => [
                'uid' => 'I77',
                'expected' => 'IP',
            ],
            'R => Reuse' => [
                'uid' => 'R01',
                'expected' => 'Reuse',
            ],
            'P => Phone' => [
                'uid' => 'P10',
                'expected' => 'Phone',
            ],
            'X => Extra' => [
                'uid' => 'X01',
                'expected' => 'Extra',
            ],
            'unknown prefix falls back to first char' => [
                'uid' => 'Z01',
                'expected' => 'Z',
            ],
            'single-letter uid returns that letter' => [
                'uid' => 'Q',
                'expected' => 'Q',
            ],
        ];
    }
}
