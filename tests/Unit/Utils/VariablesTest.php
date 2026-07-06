<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Variables;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Variables.
 *
 * Covered:
 * - env > storage precedence for scalar getters
 * - getDB()
 * - getConfigFile()
 * - getHosts()
 * - getHost()
 * - getAdminEmail()
 * - getMailLogin()
 * - getMailPassword()
 * - getEnrichmentApi()
 * - getPepper()
 * - getForceHttps()
 * - getForgotPasswordAllowed()
 * - getEmailPhoneAllowed()
 * - getHostWithProtocol()
 * - getHostWithProtocolAndBase()
 * - getAvailableTimezones()
 * - completedConfig()
 *
 * @todo Cover numeric getters after constants fallback behavior is reviewed.
 *
 * @todo Refactor Variables to read env/config through replaceable dependencies
 *       instead of direct getenv() and tirreno('storage') calls.
 */
final class VariablesTest extends TestCase {
    private \Base $f3;

    /** @var array<string, string|false> */
    private array $envBackup = [];

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $envKeys = [
        'DATABASE_URL',
        'CONFIG_FILE',
        'SITE',
        'ADMIN_EMAIL',
        'MAIL_LOGIN',
        'MAIL_PASS',
        'ENRICHMENT_API',
        'PEPPER',
        'FORCE_HTTPS',
        'ALLOW_FORGOT_PASSWORD',
        'ALLOW_EMAIL_PHONE',
    ];

    /** @var list<string> */
    private array $f3Keys = [
        'DATABASE_URL',
        'SITE',
        'ADMIN_EMAIL',
        'MAIL_LOGIN',
        'MAIL_PASS',
        'ENRICHMENT_API',
        'PEPPER',
        'FORCE_HTTPS',
        'ALLOW_FORGOT_PASSWORD',
        'ALLOW_EMAIL_PHONE',
        'BASE',
        'timezones',
    ];

    protected function setUp(): void {
        parent::setUp();

        $this->f3 = \Base::instance();

        $this->backupEnv();
        $this->clearEnv();

        $this->backupF3();
        $this->clearF3();
    }

    protected function tearDown(): void {
        $this->clearEnv();
        $this->clearF3();

        $this->restoreEnv();
        $this->restoreF3();

        parent::tearDown();
    }

    public function testGetDbPrefersEnvOverStorage(): void {
        $this->setF3('DATABASE_URL', 'storage-db');
        $this->setEnv('DATABASE_URL', 'env-db');

        $actual = Variables::getDB();

        $this->assertSame('env-db', $actual);
    }

    public function testGetDbFallsBackToStorage(): void {
        $this->setF3('DATABASE_URL', 'storage-db');

        $actual = Variables::getDB();

        $this->assertSame('storage-db', $actual);
    }

    public function testGetConfigFileDefault(): void {
        $actual = Variables::getConfigFile();

        $this->assertSame('local/config.local.ini', $actual);
    }

    public function testGetConfigFileFromEnv(): void {
        $this->setEnv('CONFIG_FILE', 'custom.ini');

        $actual = Variables::getConfigFile();

        $this->assertSame('custom.ini', $actual);
    }

    /**
     * @dataProvider scalarGetterProvider
     */
    public function testScalarGettersPreferEnvOverStorage(
        string $storageKey,
        string $envValue,
        string $storageValue,
        string $method
    ): void {
        $this->setF3($storageKey, $storageValue);
        $this->setEnv($storageKey, $envValue);

        $actual = Variables::$method();

        $this->assertSame($envValue, $actual);
    }

    public static function scalarGetterProvider(): array {
        return [
            'admin email' => [
                'storageKey' => 'ADMIN_EMAIL',
                'envValue' => 'env-admin@example.com',
                'storageValue' => 'storage-admin@example.com',
                'method' => 'getAdminEmail',
            ],
            'mail login' => [
                'storageKey' => 'MAIL_LOGIN',
                'envValue' => 'env-mail-login',
                'storageValue' => 'storage-mail-login',
                'method' => 'getMailLogin',
            ],
            'mail password' => [
                'storageKey' => 'MAIL_PASS',
                'envValue' => 'env-mail-pass',
                'storageValue' => 'storage-mail-pass',
                'method' => 'getMailPassword',
            ],
            'enrichment api' => [
                'storageKey' => 'ENRICHMENT_API',
                'envValue' => 'env-enrichment-api',
                'storageValue' => 'storage-enrichment-api',
                'method' => 'getEnrichmentApi',
            ],
            'pepper' => [
                'storageKey' => 'PEPPER',
                'envValue' => 'env-pepper',
                'storageValue' => 'storage-pepper',
                'method' => 'getPepper',
            ],
        ];
    }

    /**
     * @dataProvider scalarGetterStorageFallbackProvider
     */
    public function testScalarGettersFallBackToStorage(
        string $storageKey,
        string $storageValue,
        string $method
    ): void {
        $this->setF3($storageKey, $storageValue);

        $actual = Variables::$method();

        $this->assertSame($storageValue, $actual);
    }

    public static function scalarGetterStorageFallbackProvider(): array {
        return [
            'admin email' => [
                'storageKey' => 'ADMIN_EMAIL',
                'storageValue' => 'storage-admin@example.com',
                'method' => 'getAdminEmail',
            ],
            'mail login' => [
                'storageKey' => 'MAIL_LOGIN',
                'storageValue' => 'storage-mail-login',
                'method' => 'getMailLogin',
            ],
            'mail password' => [
                'storageKey' => 'MAIL_PASS',
                'storageValue' => 'storage-mail-pass',
                'method' => 'getMailPassword',
            ],
            'enrichment api' => [
                'storageKey' => 'ENRICHMENT_API',
                'storageValue' => 'storage-enrichment-api',
                'method' => 'getEnrichmentApi',
            ],
            'pepper' => [
                'storageKey' => 'PEPPER',
                'storageValue' => 'storage-pepper',
                'method' => 'getPepper',
            ],
        ];
    }

    public function testGetHostsFromEnv(): void {
        $this->setEnv('SITE', 'a.example,b.example');

        $actual = Variables::getHosts();

        $this->assertSame(['a.example', 'b.example'], $actual);
    }

    public function testGetHostsFromStorageArray(): void {
        $this->setF3('SITE', ['a.example', 'b.example']);

        $actual = Variables::getHosts();

        $this->assertSame(['a.example', 'b.example'], $actual);
    }

    public function testGetHostsFromStorageScalar(): void {
        $this->setF3('SITE', 'single.example');

        $actual = Variables::getHosts();

        $this->assertSame(['single.example'], $actual);
    }

    public function testGetHostReturnsFirstHost(): void {
        $this->setF3('SITE', ['a.example', 'b.example']);

        $actual = Variables::getHost();

        $this->assertSame('a.example', $actual);
    }

    public function testForceHttpsTrueFromEnv(): void {
        $this->setEnv('FORCE_HTTPS', 'true');

        $actual = Variables::getForceHttps();

        $this->assertTrue($actual);
    }

    public function testForceHttpsFalseFromEnv(): void {
        $this->setEnv('FORCE_HTTPS', 'false');

        $actual = Variables::getForceHttps();

        $this->assertFalse($actual);
    }

    public function testForgotPasswordAllowedTrue(): void {
        $this->setEnv('ALLOW_FORGOT_PASSWORD', 'true');

        $actual = Variables::getForgotPasswordAllowed();

        $this->assertTrue($actual);
    }

    public function testEmailPhoneAllowedTrue(): void {
        $this->setEnv('ALLOW_EMAIL_PHONE', 'true');

        $actual = Variables::getEmailPhoneAllowed();

        $this->assertTrue($actual);
    }

    public function testHostWithProtocolHttps(): void {
        $this->setF3('SITE', 'example.com');
        $this->setEnv('FORCE_HTTPS', 'true');

        $actual = Variables::getHostWithProtocol();

        $this->assertSame('https://example.com', $actual);
    }

    public function testHostWithProtocolHttp(): void {
        $this->setF3('SITE', 'example.com');
        $this->setEnv('FORCE_HTTPS', 'false');

        $actual = Variables::getHostWithProtocol();

        $this->assertSame('http://example.com', $actual);
    }

    public function testHostWithProtocolWrapsIpv6Host(): void {
        $this->setF3('SITE', '::1');
        $this->setEnv('FORCE_HTTPS', 'true');

        $actual = Variables::getHostWithProtocol();

        $this->assertSame('https://[::1]', $actual);
    }

    public function testHostWithProtocolDoesNotDoubleWrapIpv6Host(): void {
        $this->setF3('SITE', '[::1]');
        $this->setEnv('FORCE_HTTPS', 'true');

        $actual = Variables::getHostWithProtocol();

        $this->assertSame('https://[::1]', $actual);
    }

    public function testHostWithProtocolAndBase(): void {
        $this->setF3('SITE', 'example.com');
        $this->setF3('BASE', '/base');
        $this->setEnv('FORCE_HTTPS', 'true');

        $actual = Variables::getHostWithProtocolAndBase();

        $this->assertSame('https://example.com/base', $actual);
    }

    public function testAvailableTimezonesFiltersInvalid(): void {
        $this->setF3('timezones', [
            'UTC' => 'UTC',
            'Europe/Kyiv' => 'Kyiv',
            'Invalid/Zone' => 'Nope',
        ]);

        $actual = Variables::getAvailableTimezones();

        $this->assertArrayHasKey('UTC', $actual);
        $this->assertArrayHasKey('Europe/Kyiv', $actual);
        $this->assertArrayNotHasKey('Invalid/Zone', $actual);
    }

    public function testCompletedConfigFalseWhenMissingRequiredValue(): void {
        $this->setEnv('SITE', 'example.com');
        $this->setEnv('PEPPER', 'pepper');
        $this->setEnv('ENRICHMENT_API', 'api');

        $actual = Variables::completedConfig();

        $this->assertFalse($actual);
    }

    public function testCompletedConfigTrueFromEnv(): void {
        $this->setEnv('SITE', 'example.com');
        $this->setEnv('PEPPER', 'pepper');
        $this->setEnv('ENRICHMENT_API', 'api');
        $this->setEnv('DATABASE_URL', 'db');

        $actual = Variables::completedConfig();

        $this->assertTrue($actual);
    }

    public function testCompletedConfigTrueFromStorage(): void {
        $this->setF3('SITE', 'example.com');
        $this->setF3('PEPPER', 'pepper');
        $this->setF3('ENRICHMENT_API', 'api');
        $this->setF3('DATABASE_URL', 'db');

        $actual = Variables::completedConfig();

        $this->assertTrue($actual);
    }

    private function setEnv(string $key, string $value): void {
        putenv($key . '=' . $value);
    }

    private function backupEnv(): void {
        foreach ($this->envKeys as $key) {
            $this->envBackup[$key] = getenv($key);
        }
    }

    private function clearEnv(): void {
        foreach ($this->envKeys as $key) {
            putenv($key);
        }
    }

    private function restoreEnv(): void {
        foreach ($this->envBackup as $key => $value) {
            if ($value === false) {
                putenv($key);
                continue;
            }

            putenv($key . '=' . $value);
        }
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
