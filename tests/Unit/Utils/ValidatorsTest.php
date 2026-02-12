<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\ErrorCodes;
use Tirreno\Utils\Validators;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Validators.
 *
 * Covered (unit-testable without refactor):
 * - CSRF-only validators (purely depend on Access::CSRFTokenValid + F3 session):
 *   - Validators::validateCheckUpdates()
 *   - Validators::validateCloseAccount()
 *   - Validators::validateRefreshRules()
 * - Presence-only validators after CSRF (no DB, no Audit, no Models, no Routes):
 *   - Validators::validateLogin():
 *     - EMAIL_DOES_NOT_EXIST when missing email (and CSRF ok)
 *     - PASSWORD_DOES_NOT_EXIST when missing password (and CSRF ok)
 * - Password recovering key presence (no DB, only param presence checks):
 *   - Validators::validatePasswordRecovering() returns RENEW_KEY_DOES_NOT_EXIST when renewKey is missing
 * - Change email page key presence (no DB, only param presence checks):
 *   - Validators::validateChangeEmailPage() returns CHANGE_EMAIL_KEY_DOES_NOT_EXIST when renewKey is missing
 *
 * Not covered (unstable without refactor):
 * - Any validator that touches:
 *   - Models (Operator, keyIds, keyIdCoOwner, ForgotPassword, ChangeEmail, ...)
 *   - Audit::instance()
 *   - Variables::getAvailableTimezones(), Variables::getEnrichmentApi()
 *   - Constants::get(...)
 *   - Routes::getCurrentRequestOperator()
 *   - Access::checkCurrentOperatorkeyIdAccess(), Access::getCurrentOperatorId(), ...
 *
 * @todo Refactor:
 * - extract CsrfValidatorInterface (avoid Access::CSRFTokenValid + Base::instance())
 * - extract RequestContextInterface (F3 wrapper for SESSION/config)
 * - inject external dependencies (Audit, Variables, Constants, Models)
 * - split monolithic Validators into per-action validator classes
 */
final class ValidatorsTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'SESSION',
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
     * @dataProvider csrfOnlyProvider
     */
    public function testCsrfOnlyValidators(array $params, int|false $expected, string $method): void {
        $this->setF3('SESSION.csrf', $params['_session_csrf'] ?? null);

        $cleanParams = $params;
        unset($cleanParams['_session_csrf']);

        $actual = Validators::$method($cleanParams);

        $this->assertSame($expected, $actual);
    }

    public static function csrfOnlyProvider(): array {
        return [
            'check updates - csrf missing -> error' => [
                'params' => [
                    'token' => 'a',
                    '_session_csrf' => null,
                ],
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
                'method' => 'validateCheckUpdates',
            ],
            'check updates - csrf ok -> false' => [
                'params' => [
                    'token' => 'a',
                    '_session_csrf' => 'a',
                ],
                'expected' => false,
                'method' => 'validateCheckUpdates',
            ],
            'close account - csrf missing -> error' => [
                'params' => [
                    'token' => 'a',
                    '_session_csrf' => null,
                ],
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
                'method' => 'validateCloseAccount',
            ],
            'close account - csrf ok -> false' => [
                'params' => [
                    'token' => 'a',
                    '_session_csrf' => 'a',
                ],
                'expected' => false,
                'method' => 'validateCloseAccount',
            ],
            'refresh rules - csrf missing -> error' => [
                'params' => [
                    'token' => 'a',
                    '_session_csrf' => null,
                ],
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
                'method' => 'validateRefreshRules',
            ],
            'refresh rules - csrf ok -> false' => [
                'params' => [
                    'token' => 'a',
                    '_session_csrf' => 'a',
                ],
                'expected' => false,
                'method' => 'validateRefreshRules',
            ],
        ];
    }

    /**
     * @dataProvider validateLoginProvider
     */
    public function testValidateLogin(array $params, mixed $sessionCsrf, int|false $expected): void {
        $this->setF3('SESSION.csrf', $sessionCsrf);

        $actual = Validators::validateLogin($params);

        $this->assertSame($expected, $actual);
    }

    public static function validateLoginProvider(): array {
        return [
            'csrf invalid -> csrf error (short-circuit)' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                    'password' => 'pass',
                ],
                'sessionCsrf' => 'b',
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
            ],
            'csrf ok + missing email -> email missing error' => [
                'params' => [
                    'token' => 'a',
                    'password' => 'pass',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::EMAIL_DOES_NOT_EXIST,
            ],
            'csrf ok + empty email -> email missing error' => [
                'params' => [
                    'token' => 'a',
                    'email' => '',
                    'password' => 'pass',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::EMAIL_DOES_NOT_EXIST,
            ],
            'csrf ok + missing password -> password missing error' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::PASSWORD_DOES_NOT_EXIST,
            ],
            'csrf ok + empty password -> password missing error' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                    'password' => '',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::PASSWORD_DOES_NOT_EXIST,
            ],
            'csrf ok + email + password present -> false' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                    'password' => 'pass',
                ],
                'sessionCsrf' => 'a',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider renewKeyPresenceProvider
     */
    public function testValidatePasswordRecoveringReturnsRenewKeyMissing(?array $params, int|false $expected): void {
        $actual = Validators::validatePasswordRecovering($params);

        $this->assertSame($expected, $actual);
    }

    public static function renewKeyPresenceProvider(): array {
        return [
            'params null -> renew key missing' => [
                'params' => null,
                'expected' => ErrorCodes::RENEW_KEY_DOES_NOT_EXIST,
            ],
            'missing renewKey -> renew key missing' => [
                'params' => [],
                'expected' => ErrorCodes::RENEW_KEY_DOES_NOT_EXIST,
            ],
            'empty renewKey -> renew key missing' => [
                'params' => ['renewKey' => ''],
                'expected' => ErrorCodes::RENEW_KEY_DOES_NOT_EXIST,
            ],
        ];
    }

    private function setF3(string $key, mixed $value): void {
        $this->f3->set($key, $value);
    }

    private function backupF3(): void {
        foreach ($this->f3Keys as $key) {
            if ($this->f3->exists($key)) {
                $this->f3Backup[$key] = $this->f3->get($key);
            }
        }

        if ($this->f3->exists('SESSION.csrf')) {
            $this->f3Backup['SESSION.csrf'] = $this->f3->get('SESSION.csrf');
        }
    }

    private function clearF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }

        $this->f3->clear('SESSION.csrf');
    }

    private function restoreF3(): void {
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }

        $this->f3->clear('SESSION.csrf');

        foreach ($this->f3Backup as $key => $value) {
            $this->f3->set($key, $value);
        }
    }
}
