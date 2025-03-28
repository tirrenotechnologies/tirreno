<?php

/**
 * Tirreno ~ Open source user analytics
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

namespace Models;

class Updates extends \Models\BaseSql {
    protected $DB_TABLE_NAME = 'dshb_updates';

    protected $db;

    public function __construct($f3) {
        $this->f3 = $f3;
        $db = $this->getDbConnection(\Utils\Variables::getDB());
        $this->f3->set('API_DATABASE', $db);
        $this->db = $db;
        \DB\SQL\Mapper::__construct($db, $this->DB_TABLE_NAME, $this->DB_TABLE_FIELDS, $this->DB_TABLE_TTL);
        $this->db = $db;
        $this->createIfNotExists();
    }

    private function getDbConnection(string $url): ?\DB\SQL {
        $urlComponents = parse_url($url);

        $host = $urlComponents['host'];
        $port = $urlComponents['port'];
        $user = $urlComponents['user'];
        $pass = $urlComponents['pass'];
        $db = ltrim($urlComponents['path'], '/');

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $db);
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        try {
            return new \DB\SQL($dsn, $user, $pass, $options);
        } catch (\Exception $e) {
            throw new \Exception('Failed to establish database connection: ' . $e->getMessage());
        }
    }

    public function checkDb(string $service, array $updatesList) {
        try {
            $this->db->begin();
            foreach ($updatesList as $migration) {
                if (!$migration::isApplied($this)) {
                    $migration::up($this->db);
                    $this->add($migration::$version, $service);
                }
            }
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollback();
            error_log($e->getMessage());
            throw $e;
        }
    }

    public function isApplied(string $version, string $name): bool {
        $params = [
            ':version'  => $version,
            ':service'  => $name,
        ];

        $query = 'SELECT 1 FROM dshb_updates WHERE version = :version and service = :service LIMIT 1';

        $results = $this->execQuery($query, $params);

        return (bool) count($results);
    }

    public function add(string $version, string $name): void {
        $params = [
            ':version'  => $version,
            ':service'  => $name,
        ];

        $query = 'INSERT INTO dshb_updates (service, version) VALUES (:service, :version)';

        $this->execQuery($query, $params);
    }

    private function createIfNotExists(): void {
        $query = 'SELECT 1 FROM information_schema.tables WHERE table_name = \'dshb_updates\'';

        if (count($this->execQuery($query, null))) {
            return;
        }

        $queries = [
            ('CREATE SEQUENCE IF NOT EXISTS dshb_updates_id_seq
                AS BIGINT
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1'),
            ('CREATE TABLE IF NOT EXISTS dshb_updates (
                id bigint NOT NULL DEFAULT nextval(\'dshb_updates_id_seq\'::regclass),
                service varchar(30),
                version varchar(30),
                created timestamp without time zone DEFAULT now() NOT NULL
            )'),
            'ALTER SEQUENCE dshb_updates_id_seq OWNED BY dshb_updates.id',
            'ALTER TABLE ONLY dshb_updates ADD CONSTRAINT dshb_updates_service_version_key UNIQUE (service, version)',
            'ALTER TABLE ONLY dshb_updates ADD CONSTRAINT dshb_updates_id_pkey PRIMARY KEY (id)',
        ];

        foreach ($queries as $query) {
            $this->execQuery($query, null);
        }
    }
}
