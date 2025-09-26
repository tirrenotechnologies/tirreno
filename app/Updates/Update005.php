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

class Update005 extends Base {
    public static $version = 'v0.9.9';

    public static function apply($db) {
        $queries = [
            'CREATE INDEX event_ua_parsed_device_idx ON event_ua_parsed USING btree (device)',
            'ALTER TABLE event_device DROP CONSTRAINT event_device_account_id_key_user_agent_key',
            'ALTER TABLE ONLY event_device ADD CONSTRAINT event_device_account_id_key_user_agent_lang_key UNIQUE (account_id, key, user_agent, lang)',
            ('CREATE SEQUENCE event_session_stat_id_seq
                AS BIGINT
                START WITH 1
                INCREMENT BY 1
                NO MINVALUE
                NO MAXVALUE
                CACHE 1;
            '),
            ('CREATE TABLE event_session_stat (
                id BIGINT NOT NULL DEFAULT nextval(\'event_session_stat_id_seq\'::regclass),
                session_id BIGINT NOT NULL,
                -- account_id BIGINT NOT NULL,
                key smallint NOT NULL,
                created timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                updated timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
                -- started timestamp without time zone NOT NULL,
                -- ended timestamp without time zone,
                duration integer,
                ip_count integer,
                device_count integer,
                event_count integer,
                country_count integer,
                new_ip_count integer,
                new_device_count integer,
                http_codes jsonb DEFAULT \'[]\'::jsonb,
                http_methods jsonb DEFAULT \'[]\'::jsonb,
                event_types jsonb DEFAULT \'[]\'::jsonb,
                completed boolean DEFAULT FALSE
            )'),
            'ALTER SEQUENCE event_session_stat_id_seq OWNED BY event_session_stat.id',
            //'CREATE INDEX event_session_stat_account_id_idx ON event_session_stat USING btree (account_id)',
            'CREATE UNIQUE INDEX event_session_stat_session_id_uidx ON event_session_stat USING btree (session_id)',
            'CREATE INDEX event_session_stat_key_idx ON event_session_stat USING btree (key)',
            'ALTER TABLE ONLY event_session_stat ADD CONSTRAINT event_session_stat_id_pkey PRIMARY KEY (id)',
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }
    }
}
