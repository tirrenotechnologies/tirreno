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

class Update002 extends Base {
    public static $version = 'v0.9.6';

    private static $rulesMap = [
        29  => 'E20',
        24  => 'B19',
        19  => 'B04',
        44  => 'E27',
        4   => 'B07',
        1   => 'I01',
        9   => 'E19',
        99  => 'E05',
        82  => 'E10',
        10  => 'D06',
        15  => 'E13',
        2   => 'I06',
        102 => 'E08',
        84  => 'P02',
        23  => 'I08',
        63  => 'D02',
        100 => 'E06',
        7   => 'E12',
        87  => 'B08',
        74  => 'E24',
        20  => 'B05',
        46  => 'C01',
        8   => 'E16',
        3   => 'E01',
        109 => 'B23',
        16  => 'A01',
        25  => 'D07',
        11  => 'B01',
        65  => 'D03',
        67  => 'A03',
        54  => 'C08',
        50  => 'C04',
        17  => 'B02',
        81  => 'D08',
        47  => 'C02',
        62  => 'D01',
        18  => 'B03',
        22  => 'B06',
        14  => 'E11',
        72  => 'A08',
        86  => 'E15',
        35  => 'C16',
        12  => 'I09',
        5   => 'E17',
        6   => 'E02',
        106 => 'R03',
        48  => 'P04',
        104 => 'R01',
        105 => 'R02',
        41  => 'B20',
        45  => 'E28',
        88  => 'B09',
        97  => 'E03',
        77  => 'B12',
        78  => 'D09',
        85  => 'P01',
        94  => 'B16',
        57  => 'I03',
        40  => 'B18',
        58  => 'B21',
        69  => 'A05',
        75  => 'E25',
        89  => 'B10',
        83  => 'E14',
        31  => 'E22',
        80  => 'B14',
        73  => 'E23',
        96  => 'P03',
        68  => 'A04',
        101 => 'E07',
        64  => 'I04',
        26  => 'D04',
        27  => 'D05',
        79  => 'E26',
        95  => 'B13',
        53  => 'C07',
        43  => 'I11',
        37  => 'E29',
        71  => 'A07',
        66  => 'A02',
        93  => 'B15',
        30  => 'E21',
        60  => 'I02',
        59  => 'I05',
        42  => 'B17',
        76  => 'B11',
        38  => 'E30',
        61  => 'I07',
        28  => 'I10',
        39  => 'D10',
        70  => 'A06',
        98  => 'E04',
        21  => 'C11',
        103 => 'E09',
        49  => 'C03',
        52  => 'C06',
        108 => 'I12',
        33  => 'C14',
        55  => 'C09',
        56  => 'C10',
        32  => 'C13',
        36  => 'C12',
        107 => 'B22',
        51  => 'C05',
        34  => 'C15',
        110 => 'B24',
    ];

    public static function up($db) {
        $queries = [
            'INSERT INTO dshb_rules (id) VALUES (109), (110)',
            'CREATE INDEX event_account_lastseen_key_idx ON event_account USING btree (lastseen, key)',
            'CREATE INDEX event_url_lastseen_key_idx ON event_url USING btree (lastseen, key)',
            'CREATE INDEX event_time_key_idx ON event USING btree (time, key)',
            'CREATE INDEX event_account_latest_decision_key_idx ON event_account USING btree (latest_decision, key)',
            'CREATE INDEX event_country_lastseen_key_idx ON event_country USING btree (lastseen, key)',
            'CREATE INDEX event_ip_lastseen_key_idx ON event_ip USING btree (lastseen, key)',
            'ALTER TABLE dshb_rules ADD COLUMN validated BOOLEAN NOT NULL DEFAULT false',
            'ALTER TABLE dshb_rules ADD COLUMN uid VARCHAR',
            'ALTER TABLE dshb_rules ADD COLUMN name VARCHAR',
            'ALTER TABLE dshb_rules ADD COLUMN descr VARCHAR',
            'ALTER TABLE dshb_rules ADD COLUMN attributes JSONB DEFAULT \'[]\' NOT NULL',
            'ALTER TABLE dshb_rules ADD COLUMN updated TIMESTAMP WITHOUT TIME ZONE DEFAULT now() NOT NULL',
            'ALTER TABLE dshb_rules ADD COLUMN missing BOOLEAN',
            'DELETE FROM dshb_rules WHERE id IN (13, 90, 91, 92)',
            'UPDATE event_error_type SET name = \'Success\' WHERE id = 0',
            'UPDATE event_error_type SET name = \'Success with warnings\' WHERE id = 1',
        ];
        foreach ($queries as $sql) {
            $db->exec($sql);
        }

        $sql        = 'SELECT id FROM dshb_rules';
        $rulesIds   = array_column($db->exec($sql), 'id');

        $rules    = self::extendIds($rulesIds);

        // extend rules data
        foreach ($rules as $id => $rule) {
            $params = [
                ':validated'    => true,
                ':id'           => $id,
                ':uid'          => $rule['uid'],
                ':name'         => $rule['name'],
                ':descr'        => $rule['descr'],
                ':attributes'   => json_encode($rule['attributes']),
            ];

            $query = (
                'INSERT INTO dshb_rules (id, uid, name, descr, validated, attributes)
                VALUES (:id, :uid, :name, :descr, :validated, :attributes)
                ON CONFLICT (id) DO UPDATE
                SET uid = EXCLUDED.uid, name = EXCLUDED.name, descr = EXCLUDED.descr,
                    validated = EXCLUDED.validated, attributes = EXCLUDED.attributes'
            );

            $db->exec($query, $params);
        }

        // add uid to dshb_operators_rules
        $sql = 'ALTER TABLE dshb_operators_rules ADD COLUMN rule_uid VARCHAR';
        $db->exec($sql);

        foreach ($rulesIds as $id) {
            $sql = 'UPDATE dshb_operators_rules SET rule_uid = :uid WHERE rule_id = :id';
            $db->exec($sql, [':id' => $id, ':uid' => self::$rulesMap[$id]]);
        }

        // update event_account score details
        $sql = 'ALTER TABLE event_account ALTER COLUMN score_details TYPE JSONB USING score_details::jsonb';
        $db->exec($sql);

        $sql = (
            'UPDATE event_account
            SET score_details = (
                SELECT jsonb_agg((elem - \'id\') || jsonb_build_object(\'uid\', dshb_rules.uid))
                FROM jsonb_array_elements(score_details) AS elem

                JOIN dshb_rules
                ON (elem->>\'id\')::int = dshb_rules.id
            )
            WHERE event_account.score_details IS NOT NULL'
        );
        $db->exec($sql);

        // cleanup
        $queries = [
            'ALTER TABLE dshb_operators_rules DROP COLUMN rule_id',
            'ALTER TABLE dshb_rules ALTER COLUMN uid SET NOT NULL',
            'ALTER TABLE dshb_rules ALTER COLUMN name SET NOT NULL',
            'ALTER TABLE dshb_rules ALTER COLUMN descr SET NOT NULL',
            'ALTER TABLE dshb_rules DROP COLUMN id',
            'ALTER TABLE dshb_rules ADD CONSTRAINT dshb_rules_uid_pkey PRIMARY KEY (uid)',
            'ALTER TABLE dshb_operators_rules ADD CONSTRAINT dshb_operators_rules_rule_uid_fkey FOREIGN KEY (rule_uid) REFERENCES dshb_rules(uid) ON DELETE CASCADE',
            'CREATE INDEX event_account_score_details_idx ON event_account USING GIN (score_details)',
        ];

        foreach ($queries as $sql) {
            $db->exec($sql);
        }
    }

    public static function extendIds(array $ids): array {
        $results = [];

        $rules = self::getCoreRulesMetadata();

        foreach ($ids as $id) {
            $uid = self::$rulesMap[$id];
            if (isset($rules[$uid])) {
                $results[$id] = $rules[$uid];
            }
        }

        return $results;
    }

    private static function getCoreRulesMetadata(): array {
        $rules = \Utils\RulesClasses::getRulesClasses(true);
        $out = [];

        foreach ($rules['imported'] as $uid => $cls) {
            try {
                $out[$uid] = [
                    'uid'           => $uid,
                    'name'          => $cls::NAME,
                    'descr'         => $cls::DESCRIPTION,
                    'attributes'    => $cls::ATTRIBUTES,
                ];
            } catch (\Throwable $e) {
                error_log('Fail on const call: ' . $e->getMessage());
            }
        }

        return $out;
    }
}
