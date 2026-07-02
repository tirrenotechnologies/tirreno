<?php

declare(strict_types=1);

namespace Tests\Unit\Utils;

use Tirreno\Utils\ApiKeys;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Utils\ApiKeys.
 *
 * Covered:
 * - ApiKeys::getCurrentOperatorApiKeyId() returns null when current request API key is missing.
 * - ApiKeys::getCurrentOperatorApiKeyString() returns null when current request API key is missing.
 * - ApiKeys::getCurrentOperatorEnrichmentKeyString() returns null when current request API key is missing.
 *
 * @todo Cover positive branches of current request API key methods after
 *       tirreno('utils')->routes current request API key resolving can be replaced in tests.
 *
 * @todo Cover ApiKeys::getOperatorApiKeys() after ApiKeys and ApiKeyCoOwner
 *       model access is extracted into replaceable dependencies or repository interfaces.
 *
 * @todo Cover ApiKeys::getFirstKeyByOperatorId() after ApiKeys and ApiKeyCoOwner
 *       model access is extracted into replaceable dependencies or repository interfaces.
 */
final class ApiKeysTest extends TestCase {
    /**
     * @dataProvider nullWhenNoCurrentApiKeyProvider
     */
    public function testReturnsNullWhenNoCurrentApiKey(string $method): void {
        $actual = ApiKeys::$method();

        $this->assertNull($actual);
    }

    public static function nullWhenNoCurrentApiKeyProvider(): array {
        return [
            'getCurrentOperatorApiKeyId' => [
                'method' => 'getCurrentOperatorApiKeyId',
            ],
            'getCurrentOperatorApiKeyString' => [
                'method' => 'getCurrentOperatorApiKeyString',
            ],
            'getCurrentOperatorEnrichmentKeyString' => [
                'method' => 'getCurrentOperatorEnrichmentKeyString',
            ],
        ];
    }
}
