<?php

declare(strict_types=1);

namespace Tests\Support\Models\Query;

final class TestQueryResult {
    public static function buildFromArray(array $result, ?int $key): object {
        return (object) [
            'result' => $result,
            'key' => $key,
        ];
    }
}
