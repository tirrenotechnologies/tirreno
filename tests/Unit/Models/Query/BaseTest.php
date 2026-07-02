<?php

declare(strict_types=1);

namespace Tests\Unit\Models\Query;

use PHPUnit\Framework\TestCase;
use Tests\Support\Models\Query\TestIpQuery;
use Tests\Support\Models\Query\TestQuery;

final class BaseTest extends TestCase {
    /**
     * join() must not trigger warnings for unknown tables.
     */
    public function testJoinIgnoresUnknownTableWithoutWarning(): void {
        $query = new TestIpQuery(10);

        $query->join('unknown_table');

        $sql = $query->exposeApplyFilters('SELECT event_ip.id');

        $this->assertSame(
            'SELECT event_ip.id FROM event_ip WHERE event_ip.key = :key',
            $sql
        );
    }

    /**
     * whereColumn() must store conditions in a valid WHERE format.
     */
    public function testWhereColumnAddsColumnComparison(): void {
        $query = new TestQuery(10);

        $query->whereColumn('event.id', '=', 'event.ip');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key AND event.id = event.ip',
            $sql
        );
    }

    /**
     * Constructor must add the default key filter.
     */
    public function testConstructorAddsKeyFilter(): void {
        $query = new TestQuery(10);

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key',
            $sql
        );
    }

    /**
     * where() must add binary conditions with parameters.
     */
    public function testWhereAddsBinaryConditionAndParam(): void {
        $query = new TestQuery(10);

        $query->where('event.id', '>', 100);

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key AND event.id::int > :val_1',
            $sql
        );
    }

    /**
     * NULL string must be converted to IS NULL.
     */
    public function testWhereConvertsNullStringToIsNull(): void {
        $query = new TestQuery(10);

        $query->where('event.deleted_at', '=', 'NULL');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key AND event.deleted_at IS NULL',
            $sql
        );
    }

    /**
     * TRUE string must be converted to IS TRUE.
     */
    public function testWhereConvertsTrueStringToIsTrue(): void {
        $query = new TestQuery(10);

        $query->where('event.active', '=', 'TRUE');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key AND event.active IS TRUE',
            $sql
        );
    }

    /**
     * FALSE string must be converted to IS FALSE.
     */
    public function testWhereConvertsFalseStringToIsFalse(): void {
        $query = new TestQuery(10);

        $query->where('event.active', '=', 'FALSE');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key AND event.active IS FALSE',
            $sql
        );
    }

    /**
     * orWhere() must add OR after an existing user condition.
     */
    public function testOrWhereAddsOrAfterExistingCondition(): void {
        $query = new TestQuery(10);

        $query
            ->where('event.id', '>', 100)
            ->orWhere('event.fraud', '=', 'TRUE');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key AND event.id::int > :val_1 OR event.fraud IS TRUE',
            $sql
        );
    }

    /**
     * IN conditions must create multiple placeholders.
     */
    public function testWhereInAddsMultipleParams(): void {
        $query = new TestQuery(10);

        $query
            ->where('event.id', 'IN', [100, 200])
            ->get();

        $this->assertSame(
            'SELECT event.id AS id, event.key AS key, event.ip AS ip, event.fraud AS fraud, event.lastseen AS lastseen, event.deleted_at AS deleted_at, event.active AS active FROM event WHERE event.key = :key AND event.id IN (:val_1, :val_2)',
            $query->lastQuery
        );

        $this->assertSame(
            [
                ':key' => 10,
                ':val_1' => 100,
                ':val_2' => 200,
            ],
            $query->lastParams
        );
    }

    /**
     * BETWEEN conditions must create two placeholders.
     */
    public function testWhereBetweenAddsRangeParams(): void {
        $query = new TestQuery(10);

        $query
            ->where('event.id', 'BETWEEN', [100, 200])
            ->get();

        $this->assertSame(
            'SELECT event.id AS id, event.key AS key, event.ip AS ip, event.fraud AS fraud, event.lastseen AS lastseen, event.deleted_at AS deleted_at, event.active AS active FROM event WHERE event.key = :key AND event.id BETWEEN :val_1 AND :val_2',
            $query->lastQuery
        );

        $this->assertSame(
            [
                ':key' => 10,
                ':val_1' => 100,
                ':val_2' => 200,
            ],
            $query->lastParams
        );
    }

    /**
     * Invalid columns must be ignored.
     */
    public function testInvalidColumnIsIgnored(): void {
        $query = new TestQuery(10);

        $query->where('unknown_column', '=', 100);

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key',
            $sql
        );
    }

    /**
     * orderBy() must add valid sorting.
     */
    public function testOrderByAddsValidDirection(): void {
        $query = new TestQuery(10);

        $query->orderBy('event.lastseen', 'DESC');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key ORDER BY event.lastseen DESC',
            $sql
        );
    }

    /**
     * orderBy() must ignore invalid directions.
     */
    public function testOrderByIgnoresInvalidDirection(): void {
        $query = new TestQuery(10);

        $query->orderBy('event.lastseen', 'DROP');

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key',
            $sql
        );
    }

    /**
     * groupBy() must add valid columns.
     */
    public function testGroupByAddsValidColumn(): void {
        $query = new TestQuery(10);

        $query->groupBy('event.fraud');

        $sql = $query->exposeApplyFilters('SELECT event.fraud');

        $this->assertSame(
            'SELECT event.fraud FROM event WHERE event.key = :key GROUP BY event.fraud',
            $sql
        );
    }

    /**
     * limit() and offset() must be applied when valid.
     */
    public function testLimitAndOffsetAreApplied(): void {
        $query = new TestQuery(10);

        $query
            ->limit(20)
            ->offset(40);

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key LIMIT 20 OFFSET 40',
            $sql
        );
    }

    /**
     * Negative limit and offset must be ignored.
     */
    public function testNegativeLimitAndOffsetAreIgnored(): void {
        $query = new TestQuery(10);

        $query
            ->limit(-1)
            ->offset(-10);

        $sql = $query->exposeApplyFilters('SELECT event.id');

        $this->assertSame(
            'SELECT event.id FROM event WHERE event.key = :key',
            $sql
        );
    }

    /**
     * find() selector must build filters, sorting and limits.
     */
    public function testFindParsesSelector(): void {
        $query = new TestQuery(10);

        $query->find('id>100, sort=-lastseen, limit=20, start=40');

        $this->assertSame(
            'SELECT event.id AS id, event.key AS key, event.ip AS ip, event.fraud AS fraud, event.lastseen AS lastseen, event.deleted_at AS deleted_at, event.active AS active FROM event WHERE event.key = :key AND event.id::text > :val_1 ORDER BY event.lastseen DESC LIMIT 20 OFFSET 40',
            $query->lastQuery
        );

        $this->assertSame(
            [
                ':key' => 10,
                ':val_1' => '100',
            ],
            $query->lastParams
        );
    }

    /**
     * get() must build SELECT SQL and pass params to execQuery().
     */
    public function testGetBuildsSelectQueryAndCapturesParams(): void {
        $query = new TestQuery(10);

        $result = $query
            ->where('event.id', '>', 100)
            ->orderBy('event.lastseen', 'DESC')
            ->limit(20)
            ->get();

        $this->assertSame(
            'SELECT event.id AS id, event.key AS key, event.ip AS ip, event.fraud AS fraud, event.lastseen AS lastseen, event.deleted_at AS deleted_at, event.active AS active FROM event WHERE event.key = :key AND event.id::int > :val_1 ORDER BY event.lastseen DESC LIMIT 20',
            $query->lastQuery
        );

        $this->assertSame(
            [
                ':key' => 10,
                ':val_1' => 100,
            ],
            $query->lastParams
        );

        $this->assertSame(10, $result->key);
    }

    /**
     * count() must build COUNT SQL and pass params to execQuery().
     */
    public function testCountBuildsCountQueryAndCapturesParams(): void {
        $query = new TestQuery(10);

        $query->count('id>100, sort=-lastseen, limit=20');

        $this->assertSame(
            'SELECT COUNT(*) FROM event WHERE event.key = :key AND event.id::text > :val_1 ORDER BY event.lastseen DESC LIMIT 20',
            $query->lastQuery
        );

        $this->assertSame(
            [
                ':key' => 10,
                ':val_1' => '100',
            ],
            $query->lastParams
        );
    }
}
