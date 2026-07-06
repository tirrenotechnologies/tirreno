<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Database;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Unit tests for Tirreno\Utils\Database.
 *
 * Covered:
 * - Database::initConnect() returns true when APP_DATABASE is already set.
 * - Database::initConnect() returns false when Variables::getDB() provides no DSN.
 * - Database::getDbConnect() rejects invalid DSN formats.
 *
 * @todo Extract database connection creation from private Database::getDbConnect()
 *       into a replaceable dependency and remove Reflection from this test.
 *
 * @todo Cover successful connection creation after \DB\SQL can be replaced in tests.
 *
 * @todo Cover session wiring after \DB\SQL\Session can be replaced in tests.
 *
 * @todo Cover error response branch after tirreno('response')->error(503)
 *       can be isolated from framework side effects.
 */
final class DatabaseTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var array<string, string|false> */
    private array $envBackup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'APP_DATABASE',
        'DATABASE_URL',
    ];

    /** @var list<string> */
    private array $envKeys = [
        'DATABASE_URL',
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
        $this->clearF3();

        $this->restoreEnv();
        $this->restoreF3();

        parent::tearDown();
    }

    public function testInitConnectReturnsTrueWhenDbAlreadySet(): void {
        $database = $this->makeDbSqlWithoutConstructor();

        Database::setDb($database);

        $actual = Database::initConnect(false);

        $this->assertSame(true, $actual);
    }

    public function testInitConnectReturnsFalseWhenNoDsnProvided(): void {
        $actual = Database::initConnect(false);

        $this->assertSame(false, $actual);
    }

    public function testGetDbConnectThrowsOnInvalidDsnFormat(): void {
        $method = $this->getPrivateStaticMethod(Database::class, 'getDbConnect');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid DSN format');

        $method->invoke(null, 'not-a-dsn');
    }

    public function testGetDbConnectThrowsOnMissingParts(): void {
        $method = $this->getPrivateStaticMethod(Database::class, 'getDbConnect');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid DSN format');

        $method->invoke(null, 'pgsql://');
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
        foreach ($this->f3Keys as $key) {
            $this->f3->clear($key);
        }

        foreach ($this->f3Backup as $key => $value) {
            $this->f3->set($key, $value);
        }
    }

    private function makeDbSqlWithoutConstructor(): \DB\SQL {
        $class = new ReflectionClass(\DB\SQL::class);
        $instance = $class->newInstanceWithoutConstructor();

        /** @var \DB\SQL $database */
        $database = $instance;

        return $database;
    }

    private function getPrivateStaticMethod(string $className, string $methodName): ReflectionMethod {
        $class = new ReflectionClass($className);

        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
