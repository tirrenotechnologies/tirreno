<?php

/**
 * tirreno ~ open-source security framework
 * Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Tirreno Technologies Sàrl (https://www.tirreno.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.tirreno.com Tirreno(tm)
 */

declare(strict_types=1);

namespace Tirreno\Models\Query;

class Base {
    protected ?array $search;

    protected ?array $where = null;
    protected ?array $join  = null;         // TODO: add joinedTables list
    protected ?array $order = null;
    protected ?array $group = null;
    protected ?int $limit   = null;
    protected ?int $offset  = null;

    protected ?array $params;

    protected string $table;
    protected array $fields;
    protected array $fieldsMap;
    protected array $searchFields;

    protected bool $extraJoin = false;
    protected string $model;
    protected ?int $key = null;

    protected array $binaryOperators = [
        '=',
        '<',
        '>',
        '<=',
        '>=',
        '<>',
        '!=',
        '~',
        '*~',
        '!~',
        '!*~',
        'LIKE',
        'ILIKE',
        'NOT LIKE',
        'NOT ILIKE',
        'IN',
        'NOT IN',
        'BETWEEN',
        'NOT BETWEEN',
    ];

    protected array $unaryOperators = [
        'IS NULL',
        'NOT',
        'IS NOT NULL',
        'IS TRUE',
        'IS FALSE',
        'IS NOT TRUE',
        'IS NOT FALSE',
        'IS UNKNOWN',
        'IS NOT UNKNOWN',
    ];

    protected string $columnRegex = '/^[a-z][a-z_]+(\.[a-z_]+)?$/';

    protected array $tables = [
        'event_ip'          => \Tirreno\Models\Query\Ips::class,
        'event_account'     => \Tirreno\Models\Query\Users::class,
        'event_url'         => \Tirreno\Models\Query\Urls::class,
        'event_query'       => \Tirreno\Models\Query\Queries::class,
        'event_referer'     => \Tirreno\Models\Query\Referers::class,
        'event_session'     => \Tirreno\Models\Query\Sessions::class,
        'event_device'      => \Tirreno\Models\Query\Devices::class,
    ];

    protected array $joinKeys = [
        'event_ip'      => 'ip',
        'event_account' => 'account',
        'event_url'     => 'url',
        'event_query'   => 'query',
        'event_referer' => 'referer',
        'event_session' => 'session_id',
        'event_device'  => 'device',
    ];

    public function __construct(int $key) {
        $this->key = $key;
        $this->where = [['AND', $this->table . '.key = :key']];
        $this->params = [':key' => $key];
        $this->fieldsMap = array_flip($this->fields);
    }

    public function join(string $table): self {
        if ($this->table === $table) {
            return $this;
        }

        if ($this->table === 'event') {
            return $this;
        }

        if (!isset($this->tables[$table])) {
            return $this;
        }

        $query = "LEFT JOIN {$table} ON event.{$this->joinKeys[$table]} = {$table}.id";

        if ($this->join === null) {
            $this->join = [];
        }

        $this->join[$table] = $query;
        $this->extraJoin = true;

        $joinedTable = new $this->tables[$table]($this->key);

        $this->fields += $joinedTable->getFields();
        $this->fieldsMap += $joinedTable->getFieldsMap();

        return $this;
    }

    public function getFields(): array {
        return $this->fields;
    }

    public function getFieldsMap(): array {
        return $this->fieldsMap;
    }

    public function whereColumn(string $column, string $operator, string $cmpCol): self {
        $operator = strtoupper($operator);
        $column = $this->verifyColumn($column);
        $cmpCol = $this->verifyColumn($cmpCol);

        if ($column && $cmpCol && in_array($operator, $this->binaryOperators)) {
            // how to apply type conversions?
            $this->where[] = ['AND', $column . ' ' . $operator . ' ' . $cmpCol];
        }

        return $this;
    }

    public function orWhere(string $column, string $operator, array|string|int|float|null $value = null): self {
        return $this->collectWhere($column, $operator, $value, 'OR');
    }

    public function andWhere(string $column, string $operator, array|string|int|float|null $value = null): self {
        return $this->collectWhere($column, $operator, $value, 'AND');
    }

    public function where(string $column, string $operator, array|string|int|float|null $value = null): self {
        return $this->andWhere($column, $operator, $value);
    }

    private function collectWhere(string $column, string $operator, array|string|int|float|null $value, string $logical): self {
        $clause = $this->buildWherePart($column, $operator, $value);

        // force first clause to join with AND if default key filter is present
        $logical = count($this->params) <= 1 ? 'AND' : $logical;

        if ($clause) {
            $this->where[] = [$logical, $clause];
        }

        return $this;
    }

    private function buildWherePart(string $column, string $operator, array|string|int|float|null $value = null): ?string {
        $operator = strtoupper($operator);

        if (is_string($value)) {
            $upper = strtoupper($value);
            if ($upper === 'NULL') {
                if ($operator === '=') {
                    $operator = 'IS NULL';
                    $value = null;
                }
                if ($operator === '!=') {
                    $operator = 'IS NOT NULL';
                    $value = null;
                }
            }

            if ($upper === 'TRUE') {
                if ($operator === '=') {
                    $operator = 'IS TRUE';
                    $value = null;
                }
                if ($operator === '!=') {
                    $operator = 'IS NOT TRUE';
                    $value = null;
                }
            }

            if ($upper === 'FALSE') {
                if ($operator === '=') {
                    $operator = 'IS FALSE';
                    $value = null;
                }
                if ($operator === '!=') {
                    $operator = 'IS NOT FALSE';
                    $value = null;
                }
            }
        }

        $column = $this->verifyColumn($column);

        if ($column) {
            if ($value === null) {
                // allow IS NULL / IS NOT NULL
                if (in_array($operator, $this->unaryOperators)) {
                    // store
                    return $column . ' ' . $operator;
                }
            } elseif (in_array($operator, $this->binaryOperators)) {
                // store (add type conversion)?? or allow type conversions for column?
                $conversion = '';
                $type = gettype($value);
                $conversion = $type === 'double' ? '::numeric' : ($type === 'integer' ? '::int' : '::text');

                $conversion = match (gettype($value)) {
                    'double'    => '::numeric',
                    'integer'   => '::int',
                    'string'    => '::text',
                    default   => '',
                };

                $isBetween = in_array($operator, ['BETWEEN', 'NOT BETWEEN']);
                $placeholder = $this->addParam($value, $isBetween);

                return $column . $conversion . ' ' . $operator . ' ' . $placeholder;
                // $value can be indeed value and should be wrapped as param, or it can be a column name
            }
        }

        return null;
    }

    public function addParam(array|string|int|float $value, bool $isBetween = false): string {
        $cnt = count($this->params);

        if (is_array($value)) {
            $placeholders = [];
            foreach ($value as $elem) {
                $key = ':val_' . strval($cnt);
                $placeholders[] = $key;
                $this->params[$key] = $elem;
                $cnt += 1;
            }

            return !$isBetween ? '(' . implode(', ', $placeholders) . ')' : implode(' AND ', $placeholders);
        }

        $key = ':val_' . strval($cnt);
        $this->params[$key] = $value;

        return $key;
    }

    public function groupBy(string $column): self {
        $column = $this->verifyColumn($column);
        if ($column) {
            if ($this->group === null) {
                $this->group = [];
            }
            $this->group[] = $column;
        }

        return $this;
    }

    public function orderBy(string $column, string $direction): self {
        $column = $this->verifyColumn($column);
        if ($column && in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            if ($this->order === null) {
                $this->order = [];
            }
            $this->order[] = $column . ' ' . strtoupper($direction);
        }

        return $this;
    }

    private function verifyColumn(string $column): ?string {
        return preg_match($this->columnRegex, $column) ? (isset($this->fields[$column]) ? $column : $this->fieldsMap[$column] ?? null) : null;
    }

    public function offset(int $offset): self {
        if ($offset >= 0) {
            $this->offset = $offset;
        }

        return $this;
    }

    public function limit(int $limit): self {
        if ($limit >= 0) {
            $this->limit = $limit;
        }

        return $this;
    }

    protected function parseSelector(string $selector): void {
        //'account=5, datacenter=true, sort=-lastseen, limit=20'

        $parts = str_getcsv($selector, ',', '"', '\\');
        $where = [];
        foreach ($parts as $part) {
            $elem = trim($part);

            if (str_starts_with($elem, 'limit=')) {         // limit
                $pp = explode('=', $elem);
                if (count($pp) === 2) {
                    $limit = tirreno('utils')->conversion->intVal($pp[1]);
                    if ($limit !== null && $limit >= 0) {
                        $this->limit = $limit;
                    }
                }
            } elseif (str_starts_with($elem, 'sort=')) {    // order
                $pp = explode('=', $elem);
                if (count($pp) === 2 && preg_match('/^\-?[a-z][a-z_]+(\.[a-z_]+)?$/', $pp[1])) {
                    $column = ltrim($pp[1], '-');
                    $direction = $pp[1][0] === '-' ? 'DESC' : 'ASC';

                    $this->orderBy($column, $direction);
                }
            //} elseif (str_starts_with($elem, 'group=')) {
            } elseif (str_starts_with($elem, 'start=')) {   // offset
                $pp = explode('=', $elem);
                if (count($pp) === 2) {
                    $offset = tirreno('utils')->conversion->intVal($pp[1]);
                    if ($offset !== null && $offset >= 0) {
                        $this->offset = $offset;
                    }
                }
            } else {
                $where[] = $elem;           //column`op`val
            }
        }

        $operators = [];
        foreach ($this->binaryOperators as $operator) {
            if (preg_match('/[^a-z ]/i', $operator)) {
                $operators[] = $operator;
            }
        }

        foreach ($where as $clause) {
            // find which operator suites
            // split in csv style
            // try to split right part by pipe | in csv style

            foreach ($operators as $operator) {
                $pos = strpos($clause, $operator);
                if ($pos) {
                    $parts = str_getcsv($clause, $operator, '"', '\\');
                    if (count($parts) === 2) {
                        $column = $parts[0];
                        $value = $parts[1];

                        $valueParts = str_getcsv($value, '|', '"', '\\');
                        if (count($valueParts) > 1) {
                            // multiple or
                            $ors = [];
                            foreach ($valueParts as $value) {
                                $orPart = $this->buildWherePart($column, $operator, $value);
                                if ($orPart) {
                                    $ors[] = $orPart;
                                }
                            }
                            if ($ors) {
                                $this->where[] = ['AND', implode(' OR ', $ors)];    // TODO: optimize complex and+or conditions
                            }
                        } else {
                            $this->andWhere($column, $operator, $value);
                        }
                    }
                }
            }
        }
    }

    // TODO: `or` logic with braces
    public function find(string $selector): object {
        $this->parseSelector($selector);

        return $this->get();
    }

    protected function applyFilters(string $query): string {
        $query .= ' FROM ' . $this->table;
        if ($this->join) {
            if ($this->extraJoin) {
                $this->join['event'] = "LEFT JOIN event ON {$this->table}.id = event.{$this->joinKeys[$this->table]}";
            }

            $query .= ' ' . implode(' ', array_values($this->join));
        }

        if ($this->where) {
            $where = array_merge(...$this->where);
            array_shift($where);
            $query .= ' WHERE ' . implode(' ', $where);
        }

        if ($this->group) {
            $query .= ' GROUP BY ' . implode(', ', $this->group);
        }

        if ($this->order) {
            $query .= ' ORDER BY ' . implode(', ', $this->order);
        }

        if ($this->limit !== null) {
            $query .= ' LIMIT ' . strval($this->limit);
        }

        if ($this->offset !== null) {
            $query .= ' OFFSET ' . strval($this->offset);
        }

        return $query;
    }

    public function count(?string $selector): ?int {
        if ($selector) {
            $this->parseSelector($selector);
        }

        $query = 'SELECT COUNT(*)';

        $query = $this->applyFilters($query);
        $result = $this->execQuery($query, $this->params);

        return $result['count'] ?? null;
    }

    public function get(): object {
        $lines = [];
        foreach ($this->fields as $field => $alias) {
            $lines[] = $field . ($alias ? ' AS ' . $alias : '');
        }

        $query = 'SELECT ' . implode(', ', $lines);

        $query = $this->applyFilters($query);
        $result = $this->execQuery($query, $this->params);

        $model = $this->model;

        return tirreno('entities')->$model->buildFromArray($result, $this->key);
    }

    public function execQuery(string $query, ?array $params): array|int|null {
        return tirreno('utils')->database->getDb()->exec($query, $params);
    }
}
