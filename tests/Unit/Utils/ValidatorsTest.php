<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\ErrorCodes;
use Tirreno\Utils\Validators;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Validators.
 *
 * Covered:
 * - Validators::validateCheckUpdates()
 * - Validators::validateCloseAccount()
 * - Validators::validateRefreshRules()
 * - Validators::validateLogin()
 * - Validators::validateForgotPassword()
 * - Validators::validatePasswordRecoveringPost()
 * - Validators::validatePasswordRecovering() renewKey presence branch
 *
 * @todo Cover validators that depend on models, Audit, variables,
 *       constants, routes, assets or current operator context after those
 *       dependencies can be replaced in tests.
 *
 * @todo Split monolithic Validators into per-action validator classes.
 */
final class ValidatorsTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'SESSION',
        'MIN_PASSWORD_LENGTH',
    ];

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = \Base::instance();

        $this->backupF3();
        $this->clearF3();

        $this->f3->set('MIN_PASSWORD_LENGTH', 8);
    }

    protected function tearDown(): void {
        $this->clearF3();
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
            'csrf invalid -> csrf error' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                    'password' => 'password',
                ],
                'sessionCsrf' => 'b',
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
            ],
            'csrf ok + missing email -> email missing' => [
                'params' => [
                    'token' => 'a',
                    'password' => 'password',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::EMAIL_DOES_NOT_EXIST,
            ],
            'csrf ok + empty email -> email missing' => [
                'params' => [
                    'token' => 'a',
                    'email' => '',
                    'password' => 'password',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::EMAIL_DOES_NOT_EXIST,
            ],
            'csrf ok + missing password -> password missing' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::PASSWORD_DOES_NOT_EXIST,
            ],
            'csrf ok + empty password -> password missing' => [
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
                    'password' => 'password',
                ],
                'sessionCsrf' => 'a',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider validateForgotPasswordProvider
     */
    public function testValidateForgotPassword(array $params, mixed $sessionCsrf, int|false $expected): void {
        $this->setF3('SESSION.csrf', $sessionCsrf);

        $actual = Validators::validateForgotPassword($params);

        $this->assertSame($expected, $actual);
    }

    public static function validateForgotPasswordProvider(): array {
        return [
            'csrf invalid -> csrf error' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                ],
                'sessionCsrf' => 'b',
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
            ],
            'csrf ok + missing email -> email missing' => [
                'params' => [
                    'token' => 'a',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::EMAIL_DOES_NOT_EXIST,
            ],
            'csrf ok + empty email -> email missing' => [
                'params' => [
                    'token' => 'a',
                    'email' => '',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::EMAIL_DOES_NOT_EXIST,
            ],
            'csrf ok + email present -> false' => [
                'params' => [
                    'token' => 'a',
                    'email' => 'user@example.com',
                ],
                'sessionCsrf' => 'a',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider validatePasswordRecoveringPostProvider
     */
    public function testValidatePasswordRecoveringPost(array $params, mixed $sessionCsrf, int|false $expected): void {
        $this->setF3('SESSION.csrf', $sessionCsrf);

        $actual = Validators::validatePasswordRecoveringPost($params);

        $this->assertSame($expected, $actual);
    }

    public static function validatePasswordRecoveringPostProvider(): array {
        return [
            'csrf invalid -> csrf error' => [
                'params' => [
                    'token' => 'a',
                    'new-password' => 'password123',
                    'password-confirmation' => 'password123',
                ],
                'sessionCsrf' => 'b',
                'expected' => ErrorCodes::CSRF_ATTACK_DETECTED,
            ],
            'csrf ok + missing new password -> missing new password' => [
                'params' => [
                    'token' => 'a',
                    'password-confirmation' => 'password123',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::NEW_PASSWORD_DOES_NOT_EXIST,
            ],
            'csrf ok + short new password -> password too short' => [
                'params' => [
                    'token' => 'a',
                    'new-password' => 'short',
                    'password-confirmation' => 'short',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::PASSWORD_IS_TOO_SHORT,
            ],
            'csrf ok + missing confirmation -> confirmation missing' => [
                'params' => [
                    'token' => 'a',
                    'new-password' => 'password123',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::PASSWORD_CONFIRMATION_MISSING,
            ],
            'csrf ok + passwords mismatch -> passwords not equal' => [
                'params' => [
                    'token' => 'a',
                    'new-password' => 'password123',
                    'password-confirmation' => 'password456',
                ],
                'sessionCsrf' => 'a',
                'expected' => ErrorCodes::PASSWORDS_ARE_NOT_EQUAL,
            ],
            'csrf ok + valid passwords -> false' => [
                'params' => [
                    'token' => 'a',
                    'new-password' => 'password123',
                    'password-confirmation' => 'password123',
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
                'params' => [
                    'renewKey' => '',
                ],
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
        foreach ($this->f3Backup as $key => $value) {
            $this->f3->set($key, $value);
        }
    }
}
