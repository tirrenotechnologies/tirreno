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

namespace Updates;

class Update004 extends Base {
    public static $version = 'v0.9.8';

    public static function apply($db) {
        $data = [':type' => \Utils\Constants::get('FIELD_EDIT_EVENT_TYPE_ID')];

        $queries = [
            ('CREATE SEQUENCE event_field_audit_trail_id_seq
                AS BIGINT
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;
            '),
            ('CREATE TABLE event_field_audit_trail (
                id BIGINT NOT NULL DEFAULT nextval(\'event_field_audit_trail_id_seq\'::regclass),
                account_id BIGINT NOT NULL,
                key smallint NOT NULL,
                created timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                event_id BIGINT,
                field_id varchar,
                field_name varchar,
                old_value varchar,
                new_value varchar,
                parent_id varchar,
                parent_name varchar
            )'),
            'ALTER SEQUENCE event_field_audit_trail_id_seq OWNED BY event_field_audit_trail.id',
            'CREATE INDEX event_field_audit_trail_account_id_idx ON event_field_audit_trail USING btree (account_id)',
            'CREATE INDEX event_field_audit_trail_key_idx ON event_field_audit_trail USING btree (key)',
            'ALTER TABLE ONLY event_field_audit_trail ADD CONSTRAINT event_field_audit_trail_id_pkey PRIMARY KEY (id)',
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }

        $sql = 'INSERT INTO event_type (id, value, name) VALUES (:type, \'field_edit\', \'Field Edit\')';
        $db->exec($sql, $data);

        $queries = [
            ('CREATE SEQUENCE event_payload_id_seq
                AS BIGINT
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;
            '),
            ('CREATE TABLE event_payload (
                id BIGINT NOT NULL DEFAULT nextval(\'event_payload_id_seq\'::regclass),
                key smallint NOT NULL,
                created timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                payload json
            )'),
            'ALTER SEQUENCE event_payload_id_seq OWNED BY event_payload.id',
            'CREATE INDEX event_payload_created_idx ON event_payload USING btree (created)',
            'CREATE INDEX event_payload_key_idx ON event_payload USING btree (key)',
            'ALTER TABLE ONLY event_payload ADD CONSTRAINT event_payload_id_pkey PRIMARY KEY (id)',
            'ALTER TABLE event DROP COLUMN payload',
            'ALTER TABLE event ADD COLUMN payload BIGINT',
            'CREATE INDEX event_payload_idx ON event USING btree (payload)',
            'ALTER TABLE ONLY event ADD CONSTRAINT event_payload_fkey FOREIGN KEY (payload) REFERENCES event_payload(id)',
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }
    }
}
