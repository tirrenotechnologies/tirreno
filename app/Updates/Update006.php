<?php

/**
 * tirreno ~ open security analytics
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

namespace Updates;

class Update006 extends Base {
    public static $version = 'v0.9.10';

    public static function apply($database) {
        $queries = [
            ('CREATE SEQUENCE event_field_audit_id_seq
                AS BIGINT
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;
            '),
            ('CREATE TABLE event_field_audit (
                id BIGINT NOT NULL DEFAULT nextval(\'event_field_audit_id_seq\'::regclass),
                key smallint NOT NULL,
                field_id text NOT NULL,
                field_name text,
                lastseen timestamp without time zone NOT NULL,
                created timestamp without time zone DEFAULT now() NOT NULL,
                updated timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                total_visit integer DEFAULT 0,
                total_account integer DEFAULT 0,
                total_edit integer DEFAULT 0
            )'),
            'ALTER SEQUENCE event_field_audit_id_seq OWNED BY event_field_audit.id',
            'CREATE INDEX event_field_audit_key_idx ON event_field_audit USING btree (key)',
            'CREATE INDEX event_field_audit_field_id_idx ON event_field_audit USING btree (field_id)',
            'CREATE INDEX event_field_audit_lastseen_idx ON event_field_audit USING btree (lastseen)',
            'CREATE INDEX event_field_audit_lastseen_updated_idx ON event_field_audit(lastseen, updated) WHERE lastseen >= updated',

            'ALTER TABLE ONLY event_field_audit ADD CONSTRAINT event_field_audit_id_pkey PRIMARY KEY (id)',
            'ALTER TABLE ONLY event_field_audit ADD CONSTRAINT event_field_audit_field_id_key_key UNIQUE (field_id, key)',

            'ALTER TABLE ONLY event_field_audit ADD CONSTRAINT event_field_audit_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE',
            'CREATE TRIGGER restrict_update BEFORE UPDATE ON event_field_audit FOR EACH ROW EXECUTE FUNCTION restrict_update()',

            //
            'UPDATE event_field_audit_trail SET field_id = COALESCE(field_name, new_value) WHERE field_id IS NULL',

            ('INSERT INTO event_field_audit
                (key, field_id, field_name, lastseen, created, total_visit, total_account, total_edit)
              SELECT
                key AS key,
                field_id AS field_id,
                (
                    SELECT field_name
                    FROM event_field_audit_trail AS t
                    WHERE t.field_id = field_id AND t.key = key
                    ORDER BY id DESC
                    LIMIT 1
                ) AS field_name,
                MAX(created)                    AS lastseen,
                MIN(created)                    AS created,
                COUNT(DISTINCT event_id)        AS total_visit,
                COUNT(DISTINCT account_id)      AS total_account,
                COUNT(*)                        AS total_edit
              FROM
                event_field_audit_trail
              GROUP BY
                field_id, key
            '),

            'ALTER TABLE event_field_audit_trail ADD COLUMN field_id_tmp bigint',
            ('
                UPDATE event_field_audit_trail
                SET field_id_tmp = t.id
                FROM (
                    SELECT id, field_id, key FROM event_field_audit
                ) AS t
                WHERE
                    event_field_audit_trail.field_id = t.field_id AND
                    event_field_audit_trail.key = t.key
            '),
            'ALTER TABLE event_field_audit_trail DROP COLUMN field_id',
            'ALTER TABLE event_field_audit_trail RENAME COLUMN field_id_tmp TO field_id',
            'ALTER TABLE event_field_audit_trail ALTER COLUMN field_id SET NOT NULL',

            'CREATE INDEX event_field_audit_trail_field_id_idx ON event_field_audit_trail USING btree (field_id)',
            'CREATE INDEX event_field_audit_trail_event_id_idx ON event_field_audit_trail USING btree (event_id)',

            'ALTER TABLE ONLY event_field_audit_trail ADD CONSTRAINT event_field_audit_trail_field_id_fkey FOREIGN KEY (field_id) REFERENCES event_field_audit(id)',

            'ALTER TABLE ONLY event_field_audit_trail ADD CONSTRAINT event_field_audit_trail_account_id_key_fkey FOREIGN KEY (account_id, key) REFERENCES event_account(id, key) ON UPDATE CASCADE ON DELETE CASCADE',

            'INSERT INTO event_error_type (id, value, name) VALUES (4, \'rate_limit_exceeded\', \'Rate limit exceeded\')',
            'ALTER TABLE event_isp ADD COLUMN total_ip integer DEFAULT 0',
            ('
                UPDATE event_isp
                SET
                    total_ip = COALESCE(sub.total_ip, 0)
                FROM (
                    SELECT
                        event_ip.isp,
                        COUNT(*) AS total_ip
                    FROM event_ip
                    GROUP BY event_ip.isp
                ) AS sub
                RIGHT JOIN event_isp sub_isp ON sub.isp = sub_isp.id
                WHERE
                    event_isp.id = sub_isp.id
            '),
            'ALTER TABLE event_logbook ADD COLUMN endpoint text',
            'UPDATE event_logbook SET endpoint = \'/sensor/\'',
            'CREATE INDEX event_logbook_endpoint_idx ON event_logbook USING btree (endpoint)',
            'CREATE INDEX event_logbook_error_type_idx ON event_logbook USING btree (error_type)',

            'ALTER TABLE dshb_operators ADD COLUMN blacklist_users_cnt int',

            'ALTER TABLE event_url ADD COLUMN total_edit integer DEFAULT 0',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        $params = [':field_edit' => \Utils\Constants::FIELD_EDIT_EVENT_TYPE_ID];
        $sql = (
            'UPDATE event_url
            SET
                total_edit = COALESCE(sub.total_edit, 0)
            FROM (
                SELECT
                    event.url,
                    COUNT(CASE WHEN event.type = :field_edit THEN TRUE END) AS total_edit
                FROM event_field_audit_trail
                INNER JOIN event ON event_field_audit_trail.event_id = event.id
                GROUP BY event.url
            ) AS sub
            WHERE
                event_url.id = sub.url'
        );

        $database->exec($sql, $params);
    }
}
