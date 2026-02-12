<?php

namespace Tirreno\Models;

class EventType extends \Tirreno\Models\BaseSql {
    protected ?string $DB_TABLE_NAME = 'event_type';

    public function getAll(): array {
        $query = 'SELECT id, value, name FROM event_type';

        return $this->execQuery($query, null);
    }
}
