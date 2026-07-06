<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Routes;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Routes.
 *
 * Covered:
 * - Routes::getCurrentRequestOperator()
 * - Routes::getCurrentRequestApiKey()
 * - Routes::callExtra()
 *
 * @todo Cover Routes::setCurrentRequestOperator() and
 *       Routes::getCurrentSessionOperator() after current session operator
 *       resolving can be isolated from tirreno('session') and tirreno('entities').
 *
 * @todo Cover Routes::setCurrentRequestApiKey() and
 *       Routes::getCurrentSessionApiKey() after current session API key
 *       resolving can be isolated from tirreno('session'), TEST_API_KEY_ID
 *       and tirreno('entities').
 */
final class RoutesTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'CURRENT_USER',
        'CURRENT_KEY',
        'EXTRA_FOO',
    ];

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = \Base::instance();

        $this->backupF3();
        $this->clearF3();
    }

    protected function tearDown(): void {
        $this->clearF3();
        $this->restoreF3();

        parent::tearDown();
    }

    public function testGetCurrentRequestOperatorReturnsStoredValue(): void {
        $operator = $this->createStub(\Tirreno\Entities\Operator::class);
        $this->f3->set('CURRENT_USER', $operator);

        $actual = Routes::getCurrentRequestOperator();

        $this->assertSame($operator, $actual);
    }

    public function testGetCurrentRequestApiKeyReturnsStoredValue(): void {
        $this->f3->set('CURRENT_KEY', null);

        $actual = Routes::getCurrentRequestApiKey();

        $this->assertNull($actual);
    }

    /**
     * @dataProvider callExtraProvider
     */
    public function testCallExtra(string $methodName, mixed $extraValue, mixed $expected): void {
        $key = 'EXTRA_' . $methodName;

        if ($extraValue === '__unset__') {
            $this->f3->clear($key);
        } else {
            $this->f3->set($key, $extraValue);
        }

        $actual = Routes::callExtra($methodName, 'x');

        $this->assertSame($expected, $actual);
    }

    public static function callExtraProvider(): array {
        $callable = static function (string $value): string {
            return 'ok:' . $value;
        };

        return [
            'missing extra -> null' => [
                'methodName' => 'FOO',
                'extraValue' => '__unset__',
                'expected' => null,
            ],
            'extra not callable -> null' => [
                'methodName' => 'FOO',
                'extraValue' => 'not-callable',
                'expected' => null,
            ],
            'extra callable -> returns result' => [
                'methodName' => 'FOO',
                'extraValue' => $callable,
                'expected' => 'ok:x',
            ],
        ];
    }

    private function backupF3(): void {
        foreach ($this->f3Keys as $key) {
            if ($this->f3->exists($key)) {
                $this->f3Backup[$key] = $this->f3->get($key);
            }
        }
    }

    private function clearF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }
    }

    private function restoreF3(): void {
        foreach ($this->f3Backup as $key => $value) {
            $this->f3->set($key, $value);
        }
    }
}
