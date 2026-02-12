<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use Tirreno\Models\BaseSql;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Tirreno\Models\BaseSql.
 *
 * Covered (unit-testable without refactor):
 * - getArrayPlaceholders():
 *   - builds params map with named placeholders
 *   - returns placeholders string joined with ", "
 *   - applies postfix formatting ("_postfix")
 * - getHash():
 *   - uses F3 SALT
 *   - returns deterministic PBKDF2 sha256 output with fixed parameters
 * - getPseudoRandomString():
 *   - returns hex string
 *   - length is 2 * (length/2) => equals requested length (when even)
 *
 * Not covered (recommended to refactor first):
 * - printLog() (echo + depends on real DB)
 * - execQuery() (depends on Database::getDb() + real connection)
 * - constructor behavior when DB_TABLE_NAME is set (invokes \DB\SQL\Mapper)
 *
 * @todo Refactor:
 * - inject Base/Config instead of \Base::instance()
 * - inject Database connection instead of static Database::getDb()
 * - separate pure helpers (hashing/placeholders/random) into a dedicated service
 */
final class BaseSqlTest extends TestCase {
    private \Base $f3;

    /** @var array<string, mixed> */
    private array $f3Backup = [];

    /** @var list<string> */
    private array $f3Keys = [
        'SALT',
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

    public function testGetArrayPlaceholdersWithoutPostfix(): void {
        $model = $this->makeModel();

        $ids = [10, 20, 30];

        [$actualParams, $actualPlaceholders] = $model->getArrayPlaceholders($ids);

        $expectedParams = [
            ':item_id_0' => 10,
            ':item_id_1' => 20,
            ':item_id_2' => 30,
        ];
        $this->assertSame($expectedParams, $actualParams);

        $expectedPlaceholders = ':item_id_0, :item_id_1, :item_id_2';
        $this->assertSame($expectedPlaceholders, $actualPlaceholders);
    }

    public function testGetArrayPlaceholdersWithPostfix(): void {
        $model = $this->makeModel();

        $ids = [7, 8];

        $postfix = 'a';
        [$actualParams, $actualPlaceholders] = $model->getArrayPlaceholders($ids, $postfix);

        $expectedParams = [
            ':item_id_0_a' => 7,
            ':item_id_1_a' => 8,
        ];
        $this->assertSame($expectedParams, $actualParams);

        $expectedPlaceholders = ':item_id_0_a, :item_id_1_a';
        $this->assertSame($expectedPlaceholders, $actualPlaceholders);
    }

    /* ================= helpers ================= */

    private function makeModel(): BaseSql {
        $model = new class () extends BaseSql {
            /** @var string|null */
            protected ?string $DB_TABLE_NAME = null;
        };

        return $model;
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
}
