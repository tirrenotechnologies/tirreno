<?php

declare(strict_types=1);

namespace Tests\Support\Models\Query;

use Tirreno\Models\Query\Base;

final class TestQuery extends Base {
    protected string $table = 'event';

    protected array $fields = [
        'event.id' => 'id',
        'event.key' => 'key',
        'event.ip' => 'ip',
        'event.fraud' => 'fraud',
        'event.lastseen' => 'lastseen',
        'event.deleted_at' => 'deleted_at',
        'event.active' => 'active',
    ];

    protected array $searchFields = [];

    protected string $model = TestQueryResult::class;

    public ?string $lastQuery = null;

    public ?array $lastParams = null;

    public function get(): object {
        $lines = [];

        foreach ($this->fields as $field => $alias) {
            $lines[] = $field . ($alias ? ' AS ' . $alias : '');
        }

        $query = 'SELECT ' . implode(', ', $lines);

        $query = $this->applyFilters($query);
        $result = $this->execQuery($query, $this->params);

        return TestQueryResult::buildFromArray($result, $this->key);
    }

    public function exposeApplyFilters(string $query): string {
        return $this->applyFilters($query);
    }

    public function execQuery(string $query, ?array $params): array|int|null {
        $this->lastQuery = $query;
        $this->lastParams = $params;

        return [];
    }
}
