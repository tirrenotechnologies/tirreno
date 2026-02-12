<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Routes;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Routes.
 *
 * Covered (unit-testable without refactor):
 * - Routes::getCurrentRequestOperator():
 *   - returns CURRENT_USER from F3 as-is for allowed types (null / false / Operator)
 *   - we cover only null/false (Operator requires DB-backed model)
 * - Routes::callExtra():
 *   - returns null when EXTRA_* is missing
 *   - returns null when EXTRA_* is not callable
 *   - calls callable and returns its result
 *
 * Not covered (unstable without refactor):
 * - setCurrentRequestOperator() / getCurrentSessionOperator():
 *   - depends on new Models\Operator() and DB\SQL Mapper
 * - redirectIfUnlogged() / redirectIfLogged():
 *   - depends on F3 reroute() behavior (may exit/emit headers)
 *
 * @todo Refactor:
 * - extract F3 wrapper (Config/Context) instead of Base::instance()
 * - inject OperatorRepository/Reader instead of new Models\Operator()
 * - wrap reroute() into RedirectorInterface to make redirects unit-testable
 */
final class RoutesTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'CURRENT_USER',
    ];

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = \Base::instance();

        $this->backupF3();
        $this->clearF3();
    }

    protected function tearDown(): void {
        $this->restoreF3();

        parent::tearDown();
    }

    /**
     * @dataProvider currentRequestOperatorProvider
     */
    public function testGetCurrentRequestOperatorReturnsF3Value(mixed $currentUser): void {
        $this->f3->set('CURRENT_USER', $currentUser);

        $actual = Routes::getCurrentRequestOperator();

        $this->assertSame($currentUser, $actual);
    }

    public static function currentRequestOperatorProvider(): array {
        // TODO: add Operator obj
        return [
            'null' => [
                'currentUser' => null,
            ],
        ];
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

        $arg = 'x';

        $actual = Routes::callExtra($methodName, $arg);

        $this->assertSame($expected, $actual);
    }

    public static function callExtraProvider(): array {
        $callable = static function (string $value): string {
            $result = 'ok:' . $value;
            return $result;
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

        $extraFooKey = 'EXTRA_FOO';
        if ($this->f3->exists($extraFooKey)) {
            $this->f3Backup[$extraFooKey] = $this->f3->get($extraFooKey);
        }
    }

    private function clearF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }

        $this->f3->clear('EXTRA_FOO');
    }

    private function restoreF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }

        $this->f3->clear('EXTRA_FOO');

        foreach ($this->f3Backup as $key => $value) {
            $this->f3->set($key, $value);
        }
    }
}
