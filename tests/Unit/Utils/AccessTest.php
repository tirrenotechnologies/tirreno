<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\Access;
use Tirreno\Utils\ErrorCodes;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\Access.
 *
 * Covered:
 * - Access::CSRFTokenValid()
 * - Access::saltHash()
 * - Access::pseudoRandString()
 *
 * @todo Cover Access::cleanHost() after extracting host resolving from
 *       tirreno('utils')->variables static proxy into a replaceable dependency.
 *
 * @todo Cover Access::checkApiKeyAccess() after extracting ApiKeys and
 *       ApiKeyCoOwner model access into replaceable dependencies or repository interfaces.
 *
 * @todo Cover Access::checkCurrentOperatorApiKeyAccess() after current operator
 *       resolving is isolated from tirreno('utils')->routes static proxy.
 *
 * @todo Cover Access::getCurrentOperatorId() and Access::getCurrentOperatorApiKeyId()
 *       after Routes/current request context can be replaced in tests.
 *
 * @todo Cover Access::hashPassword() and Access::verifyPassword() after
 *       pepper resolving is extracted from tirreno('utils')->variables static proxy
 *       into a replaceable dependency, or after test config provides PEPPER.
 *
 * @todo Decide expected behavior of Access::pseudoRandString() for odd lengths:
 *       either reject them explicitly or generate a string with the requested length.
 */
final class AccessTest extends TestCase {
    protected function tearDown(): void {
        tirreno('session')->remove('csrf');
        tirreno('storage')->remove('SALT');

        parent::tearDown();
    }

    /**
     * @dataProvider csrfTokenValidProvider
     */
    public function testCSRFTokenValid(
        array $params,
        mixed $sessionCsrf,
        int|false $expected
    ): void {
        tirreno('session')->set('csrf', $sessionCsrf);

        $actual = Access::CSRFTokenValid($params);

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

    public function testSaltHashUsesStorageSaltAndIsDeterministic(): void {
        $salt = 'test-salt';
        $input = 'hello';

        tirreno('storage')->set('SALT', $salt);

        $expected = hash_pbkdf2(
            'sha256',
            $input,
            $salt,
            1000,
            32
        );

        $actual = Access::saltHash($input);

        $this->assertSame($expected, $actual);
    }

    public function testPseudoRandStringIsHexAndHasExpectedLength(): void {
        $length = 32;

        $actual = Access::pseudoRandString($length);

        $this->assertSame($length, strlen($actual));
        $this->assertSame(1, preg_match('/^[0-9a-f]+$/', $actual));
    }

    public function testPseudoRandStringUsesDefaultLength(): void {
        $actual = Access::pseudoRandString();

        $this->assertSame(32, strlen($actual));
        $this->assertSame(1, preg_match('/^[0-9a-f]+$/', $actual));
    }

    public function testPseudoRandStringReturnsDifferentValues(): void {
        $first = Access::pseudoRandString();
        $second = Access::pseudoRandString();

        $this->assertNotSame($first, $second);
    }
}
