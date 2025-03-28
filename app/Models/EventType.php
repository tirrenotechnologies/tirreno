<?php

namespace Models;

class EventType extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'event_type';

    public function getAll(): array {
        $query = 'SELECT id, value, name FROM event_type';

        return $this->execQuery($query, null);
    }
}
