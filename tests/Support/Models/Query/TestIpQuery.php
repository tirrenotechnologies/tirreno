<?php

declare(strict_types=1);

namespace Tests\Support\Models\Query;

use Tirreno\Models\Query\Base;

final class TestIpQuery extends Base {
    protected string $table = 'event_ip';

    protected array $fields = [
        'event_ip.id' => 'id',
        'event_ip.key' => 'key',
    ];

    protected array $searchFields = [];

    protected string $model = TestQueryResult::class;

    public function exposeApplyFilters(string $query): string {
        return $this->applyFilters($query);
    }

    public function execQuery(string $query, ?array $params): array|int|null {
        return [];
    }
}
