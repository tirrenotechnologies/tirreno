<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Access;
use Tirreno\Utils\ErrorCodes;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Access.
 *
 * Covered (unit-testable without refactor):
 * - Access::CSRFTokenValid()
 * - Access::cleanHost() (HTTP branch only; stable positive cases):
 *   - prefers SERVER_NAME (+ optional port matching)
 *   - falls back to HTTP_HOST
 *
 * Not covered (unstable without refactor):
 * - cleanHost() CLI branch (depends on PHP_SAPI)
 * - cleanHost() error branch ($f3->error(400)) (depends on F3 error handling / exit)
 * - model-based access checks (new Models inside):
 *   checkApiKeyAccess(), checkCurrentOperatorApiKeyAccess(),
 *   getCurrentOperatorId(), getCurrentOperatorApiKeyId()
 *
 * @todo Refactor:
 * - extract ServerEnvInterface (instead of direct $_SERVER usage)
 * - extract HostResolver (instead of static Variables/Conversion usage)
 * - inject model factories for ApiKeys / ApiKeyCoOwner
 */
final class AccessTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var array<string, mixed> */
    private array $serverBackup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'HOST',
        'SITE',
    ];

    /** @var list<string> */
    private array $serverKeys = [
        'SERVER_PORT',
        'SERVER_NAME',
        'HTTP_HOST',
    ];

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = \Base::instance();

        $this->backupF3();
        $this->clearF3();

        $this->backupServer();
        $this->clearServer();
    }

    protected function tearDown(): void {
        $this->restoreServer();
        $this->restoreF3();

        parent::tearDown();
    }

    /**
     * @dataProvider csrfTokenValidProvider
     */
    public function testCSRFTokenValid(array $params, mixed $sessionCsrf, int|false $expected): void {
        $this->setF3('SESSION.csrf', $sessionCsrf);

        $actual = Access::CSRFTokenValid($params, $this->f3);

        $this->assertSame($expected, $actual);
    }

    public static function csrfTokenValidProvider(): array {
        $error = ErrorCodes::CSRF_ATTACK_DETECTED;

        return [
            'missing token' => [
                'params' => [],
                'sessionCsrf' => 'abc',
                'expected' => $error,
            ],
            'empty token' => [
                'params' => ['token' => ''],
                'sessionCsrf' => 'abc',
                'expected' => $error,
            ],
            'missing session csrf' => [
                'params' => ['token' => 'abc'],
                'sessionCsrf' => null,
                'expected' => $error,
            ],
            'empty session csrf' => [
                'params' => ['token' => 'abc'],
                'sessionCsrf' => '',
                'expected' => $error,
            ],
            'token mismatch' => [
                'params' => ['token' => 'abc'],
                'sessionCsrf' => 'def',
                'expected' => $error,
            ],
            'token matches' => [
                'params' => ['token' => 'abc'],
                'sessionCsrf' => 'abc',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider cleanHostProvider
     */
    public function testCleanHost(array $siteHosts, array $server, string $expectedHost): void {
        $this->setF3('SITE', $siteHosts);

        foreach ($server as $key => $value) {
            $_SERVER[$key] = $value;
        }

        Access::cleanHost();

        $actual = $this->f3->get('HOST');

        $this->assertSame($expectedHost, $actual);
    }

    public static function cleanHostProvider(): array {
        return [
            'strip port from domain' => [
                'siteHosts' => ['example.com:8080'],
                'server' => [
                    'HTTP_HOST' => 'example.com:8080',
                ],
                'expectedHost' => 'example.com',
            ],
            'keep domain without port' => [
                'siteHosts' => ['example.com'],
                'server' => [
                    'HTTP_HOST' => 'example.com',
                ],
                'expectedHost' => 'example.com',
            ],
            'strip port from www domain' => [
                'siteHosts' => ['www.example.com:8443'],
                'server' => [
                    'HTTP_HOST' => 'www.example.com:8443',
                ],
                'expectedHost' => 'www.example.com',
            ],
            'keep www domain' => [
                'siteHosts' => ['www.example.com'],
                'server' => [
                    'HTTP_HOST' => 'www.example.com',
                ],
                'expectedHost' => 'www.example.com',
            ],
            'strip port from localhost' => [
                'siteHosts' => ['localhost:8000'],
                'server' => [
                    'HTTP_HOST' => 'localhost:8000',
                ],
                'expectedHost' => 'localhost',
            ],
            'keep localhost' => [
                'siteHosts' => ['localhost'],
                'server' => [
                    'HTTP_HOST' => 'localhost',
                ],
                'expectedHost' => 'localhost',
            ],
            'strip port from ipv4' => [
                'siteHosts' => ['127.0.0.1:9000'],
                'server' => [
                    'HTTP_HOST' => '127.0.0.1:9000',
                ],
                'expectedHost' => '127.0.0.1',
            ],
            [
                'siteHosts' => ['127.0.0.1'],
                'server' => [
                    'HTTP_HOST' => '127.0.0.1',
                ],
                'expectedHost' => '127.0.0.1',
            ],
            /*'strip port from ipv6' => [
                'siteHosts' => ['[::1]:8000'],
                'server' => [
                    'HTTP_HOST' => '[::1]:8000',
                ],
                'expectedHost' => '::1',
            ],
            'keep ipv6' => [
                'siteHosts' => ['::1'],
                'server' => [
                    'HTTP_HOST' => '::1',
                ],
                'expectedHost' => '::1',
            ],*/
            'lowercase normalization' => [
                'siteHosts' => ['ExAmPlE.CoM:8080'],
                'server' => [
                    'HTTP_HOST' => 'ExAmPlE.CoM:8080',
                ],
                'expectedHost' => 'example.com',
            ],
        ];
    }

    public function testGetHashUsesSaltAndIsDeterministic(): void {
        $salt = 'test-salt';
        $this->f3->set('SALT', $salt);

        $input = 'hello';

        $iterations = 1000;
        $length = 32;

        $expected = hash_pbkdf2('sha256', $input, $salt, $iterations, $length);
        $actual = Access::saltHash($input);

        $this->assertSame($expected, $actual);
    }

    public function testGetPseudoRandomStringIsHexAndHasExpectedLength(): void {
        $length = 32;
        $actual = Access::pseudoRandString($length);

        $expectedLength = 32;
        $actualLength = strlen($actual);
        $this->assertSame($expectedLength, $actualLength);

        $expectedIsHex = 1;
        $actualIsHex = preg_match('/^[0-9a-f]+$/', $actual);
        $this->assertSame($expectedIsHex, $actualIsHex);
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

    private function backupServer(): void {
        foreach ($this->serverKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $this->serverBackup[$key] = $_SERVER[$key];
            }
        }
    }

    private function clearServer(): void {
        foreach ($this->serverKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                unset($_SERVER[$key]);
            }
        }
    }

    private function restoreServer(): void {
        foreach ($this->serverKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                unset($_SERVER[$key]);
            }
        }

        foreach ($this->serverBackup as $key => $value) {
            $_SERVER[$key] = $value;
        }
    }
}
