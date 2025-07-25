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

class Update003 extends Base {
    public static $version = 'v0.9.7';

    private const ERROR_EVENT_TYPE = 12;

    public static function up($db) {
        $queries = [
            'ALTER TABLE event_logbook DROP COLUMN raw_time',
            'ALTER TABLE event_account ADD COLUMN added_to_review TIMESTAMP WITHOUT TIME ZONE DEFAULT NULL',
            'CREATE INDEX event_account_added_to_review_idx ON event_account USING btree (added_to_review)',
            (
                'UPDATE event_account
                SET added_to_review = event_account.lastseen
                FROM dshb_api
                WHERE
                    event_account.key = dshb_api.id AND
                    event_account.fraud IS NULL AND
                    event_account.score <= dshb_api.review_queue_threshold'
            ),
            'INSERT INTO event_type (id, value, name) VALUES (12, \'page_error\', \'Page Error\')',
            'ALTER TABLE countries RENAME COLUMN id TO iso',
            'ALTER TABLE countries RENAME COLUMN serial TO id',
            'ALTER TABLE countries DROP CONSTRAINT countries_id_pkey',
            'DROP INDEX countries_serial_uidx',
            'ALTER TABLE countries ADD CONSTRAINT countries_id_pkey PRIMARY KEY (id)',
            'CREATE UNIQUE INDEX countries_iso_uidx ON countries USING btree (iso)',
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }

        $sql = (
            'UPDATE event
            SET type = :type
            WHERE http_code >= 400'
        );

        $db->exec($sql, [':type' => self::ERROR_EVENT_TYPE]);
    }
}
