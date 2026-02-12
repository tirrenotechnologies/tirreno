<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Updates.
 *
 * Covered (unit-testable without refactor):
 * - None.
 *
 * Not covered (unstable without refactor):
 * - Updates::syncUpdates():
 *   - instantiates \Tirreno\Models\Updates($f3) and calls checkDb(...) (DB required)
 *   - may instantiate controller and call updateRules(...) (side effects)
 *   - Routes::callExtra('UPDATES') is unreachable in unit test because DB-dependent code executes before it
 *
 * @todo Refactor:
 * - extract UpdatesApplierInterface (wrap Models\Updates::checkDb)
 * - extract RulesUpdaterInterface (wrap controller updateRules)
 * - inject dependencies (avoid new ... inside method)
 * - make hook call invokable independently (or accept a callable)
 */
final class UpdatesTest extends TestCase {
    public function testSyncUpdatesIsNotUnitTestableWithoutRefactor(): void {
        $reason = 'Updates::syncUpdates() performs DB-dependent work before calling Routes::callExtra(), '
            . 'so the hook is unreachable without refactor/DI.';

        $this->markTestSkipped($reason);
    }
}
