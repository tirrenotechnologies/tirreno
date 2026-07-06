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

namespace Tirreno\Updates;

class Update009 extends Base {
    public static string $version = 'v0.10.0';

    private static int $primaryRulesSetId = 1;
    private static int $daemonId = 42;
    private static string $daemonEmail = 'daemon';
    private static int $guestId = 37;
    private static string $guestEmail = 'guest';
    private static int $reservedOperatorIds = 100;

    private static array $permissions = [
        1 => ['value' => 'page_view', 'name' => 'Page View'],
        2 => ['value' => 'page_edit', 'name' => 'Page Edit'],
        3 => ['value' => 'page_delete', 'name' => 'Page Delete'],
        4 => ['value' => 'page_publish', 'name' => 'Page Publish'],
        5 => ['value' => 'user_admin', 'name' => 'User Admin'],
    ];

    private static array $roles = [
        1 => ['value' => 'superuser', 'name' => 'Superuser'],
        2 => ['value' => 'guest', 'name' => 'Guest'],
        3 => ['value' => 'operator', 'name' => 'Operator'],
    ];

    private static int $superuserRoleId = 1;
    private static int $guestRoleId = 2;
    private static int $operatorRoleId = 3;
    private static int $pageViewPermissionId = 1;
    private static int $pageEditPermissionId = 2;

    private static array $pages = [
        'logout'            => '/logout',
        'home'              => '/',
        'events'            => '/event',
        'reviewQueue'       => '/review-queue',
        'blacklist'         => '/blacklist',
        'logbook'           => '/logbook',
        'watchlist'         => '/watchlist',
        'api'               => '/api',
        'rules'             => '/rules',
        'settings'           => '/settings',
        'manualCheck'       => '/manual-check',
        'user'              => '/id/@userId',
        'userAgent'         => '/user-agent/@userAgentId',
        'ip'                => '/ip/@ipId',
        'domain'            => '/domain/@domainId',
        'users'             => '/id',
        'userAgents'        => '/user-agent',
        'ips'               => '/ip',
        'isps'              => '/isp',
        'isp'               => '/isp/@ispId',
        'countries'         => '/country',
        'country'           => '/country/@countryId',
        'domains'           => '/domain',
        'resources'         => '/resource',
        'resource'          => '/resource/@resourceId',
        'fieldAudits'       => '/field',
        'fieldAudit'        => '/field/@fieldId',

        'cron'              => '/cron',

        'devices'           => '/device',
        'emails'            => '/email',
        'phones'            => '/phone',
        'main'              => '/main',

        'error'             => '/error',
        'signup'            => '/signup',
        'login'             => '/login',
        'forgotPassword'    => '/forgot-password',
        'passwordRecovering' => '/password-recovering/@renewKey',
    ];

    public static function apply(\DB\SQL $database): void {
        // dshb_permissions             stores permissions ids and names
        $queries = [
            self::regularSequence('dshb_permissions_id_seq'),
            ('CREATE TABLE dshb_permissions (
                id smallint NOT NULL DEFAULT nextval(\'dshb_permissions_id_seq\'::regclass),
                value text NOT NULL,
                name text NOT NULL
            )'),
            'ALTER SEQUENCE dshb_permissions_id_seq OWNED BY dshb_permissions.id',
            'ALTER TABLE ONLY dshb_permissions ADD CONSTRAINT dshb_permissions_id_pkey PRIMARY KEY (id)',
            'CREATE INDEX dshb_permissions_value_idx ON dshb_permissions USING btree (value)',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        foreach (self::$permissions as $id => $permission) {
            $sql = 'INSERT INTO dshb_permissions (id, name, value) VALUES (:id, :name, :value)';
            $params = [
                ':id'       => $id,
                ':name'     => $permission['name'],
                ':value'    => $permission['value'],
            ];
            $database->exec($sql, $params);
        }

        $sql = ('
            SELECT setval(
                pg_get_serial_sequence(\'dshb_permissions\', \'id\'),
                (SELECT max(id) FROM dshb_permissions),
                true
        )');

        $database->exec($sql);

        // dshb_roles                   stores roles ids and names, adjustable
        $queries = [
            self::regularSequence('dshb_roles_id_seq'),
            ('CREATE TABLE dshb_roles (
                id BIGINT NOT NULL DEFAULT nextval(\'dshb_roles_id_seq\'::regclass),
                value text NOT NULL,
                name text NOT NULL
            )'),
            'ALTER SEQUENCE dshb_roles_id_seq OWNED BY dshb_roles.id',
            'ALTER TABLE ONLY dshb_roles ADD CONSTRAINT dshb_roles_id_pkey PRIMARY KEY (id)',
            'CREATE INDEX dshb_roles_value_idx ON dshb_roles USING btree (value)',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        foreach (self::$roles as $id => $role) {
            $sql = 'INSERT INTO dshb_roles (id, name, value) VALUES (:id, :name, :value)';
            $params = [
                ':id'       => $id,
                ':name'     => $role['name'],
                ':value'    => $role['value'],
            ];
            $database->exec($sql, $params);
        }

        $sql = ('
            SELECT setval(
                pg_get_serial_sequence(\'dshb_roles\', \'id\'),
                (SELECT max(id) FROM dshb_permissions),
                true
        )');

        $database->exec($sql);

        // dshb_pages                   stores ids and names of all routes
        $queries = [
            self::regularSequence('dshb_pages_id_seq'),
            ('CREATE TABLE dshb_pages (
                id BIGINT NOT NULL DEFAULT nextval(\'dshb_pages_id_seq\'::regclass),
                value text NOT NULL,
                name text NOT NULL
            )'),
            'ALTER SEQUENCE dshb_pages_id_seq OWNED BY dshb_pages.id',
            'ALTER TABLE ONLY dshb_pages ADD CONSTRAINT dshb_pages_id_pkey PRIMARY KEY (id)',
            'CREATE INDEX dshb_pages_value_idx ON dshb_pages USING btree (value)',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        foreach (self::$pages as $name => $value) {
            $sql = 'INSERT INTO dshb_pages (name, value) VALUES (:name, :value)';
            $params = [
                ':name'     => $name,
                ':value'    => $value,
            ];
            $database->exec($sql, $params);
        }

        // dshb_roles_permissions       stores ids of role + permission pairs
        $queries = [
            self::regularSequence('dshb_roles_permissions_id_seq'),
            ('CREATE TABLE dshb_roles_permissions (
                id BIGINT NOT NULL DEFAULT nextval(\'dshb_roles_permissions_id_seq\'::regclass),
                role BIGINT NOT NULL,
                permission smallint NOT NULL
            )'),
            'ALTER SEQUENCE dshb_roles_permissions_id_seq OWNED BY dshb_roles_permissions.id',
            'ALTER TABLE ONLY dshb_roles_permissions ADD CONSTRAINT dshb_roles_permissions_id_pkey PRIMARY KEY (id)',
            'CREATE UNIQUE INDEX dshb_roles_permissions_role_permission_uidx ON dshb_roles_permissions USING btree (role, permission)',
            'CREATE INDEX dshb_roles_permissions_role_idx ON dshb_roles_permissions USING btree (role)',
            'CREATE INDEX dshb_roles_permissions_permission_idx ON dshb_roles_permissions USING btree (permission)',
            'ALTER TABLE ONLY dshb_roles_permissions ADD CONSTRAINT dshb_roles_permissions_role_fkey FOREIGN KEY (role) REFERENCES dshb_roles(id) ON UPDATE CASCADE ON DELETE CASCADE',
            'ALTER TABLE ONLY dshb_roles_permissions ADD CONSTRAINT dshb_roles_permissions_permission_fkey FOREIGN KEY (permission) REFERENCES dshb_permissions(id) ON UPDATE CASCADE ON DELETE CASCADE',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        // page_view for guest, all permissions for superuser, all page permissions for operator
        foreach (self::$permissions as $id => $permission) {
            $sql = 'INSERT INTO dshb_roles_permissions (role, permission) VALUES (:superuser, :permission)';
            $params = [
                ':superuser'    => self::$superuserRoleId,
                ':permission'   => $id,
            ];
            $database->exec($sql, $params);
        }

        foreach (self::$permissions as $id => $permission) {
            if ($permission['value'] === 'user_admin') {
                continue;
            }
            $sql = 'INSERT INTO dshb_roles_permissions (role, permission) VALUES (:operator, :permission)';
            $params = [
                ':operator'     => self::$operatorRoleId,
                ':permission'   => $id,
            ];
            $database->exec($sql, $params);
        }

        $sql = 'INSERT INTO dshb_roles_permissions (role, permission) VALUES (:role, :view_permission), (:role, :edit_permission)';
        $params = [
            ':role'             => self::$guestRoleId,
            ':view_permission'  => self::$pageViewPermissionId,
            ':edit_permission'  => self::$pageEditPermissionId,
        ];
        $database->exec($sql, $params);

        // dshb_pages_permissions       stores ids of role + permission + page triples
        $queries = [
            self::regularSequence('dshb_pages_permissions_id_seq'),
            ('CREATE TABLE dshb_pages_permissions (
                id BIGINT NOT NULL DEFAULT nextval(\'dshb_pages_permissions_id_seq\'::regclass),
                role_permission BIGINT NOT NULL,
                page BIGINT NOT NULL
            )'),
            'ALTER SEQUENCE dshb_pages_permissions_id_seq OWNED BY dshb_pages_permissions.id',
            'ALTER TABLE ONLY dshb_pages_permissions ADD CONSTRAINT dshb_pages_permissions_id_pkey PRIMARY KEY (id)',
            'CREATE UNIQUE INDEX dshb_pages_permissions_role_permission_page_uidx ON dshb_pages_permissions USING btree (role_permission, page)',
            'CREATE INDEX dshb_pages_permissions_role_permission_idx ON dshb_pages_permissions USING btree (role_permission)',
            'CREATE INDEX dshb_pages_permissions_page_idx ON dshb_pages_permissions USING btree (page)',
            'ALTER TABLE ONLY dshb_pages_permissions ADD CONSTRAINT dshb_pages_permissions_role_permission_fkey FOREIGN KEY (role_permission) REFERENCES dshb_roles_permissions(id) ON UPDATE CASCADE ON DELETE CASCADE',
            'ALTER TABLE ONLY dshb_pages_permissions ADD CONSTRAINT dshb_pages_permissions_page_fkey FOREIGN KEY (page) REFERENCES dshb_pages(id) ON UPDATE CASCADE ON DELETE CASCADE',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        $sql = 'SELECT id, value, name FROM dshb_pages';
        $results = $database->exec($sql);
        $pages = [];
        foreach ($results as $result) {
            $pages[$result['name']] = $result;
        }

        $guestPages = ['signup', 'login', 'forgotPassword', 'passwordRecovering', 'error'];

        $sql = 'SELECT id FROM dshb_roles_permissions WHERE role = :guest AND permission = :page_view ';
        $params = [
            ':guest'        => self::$guestRoleId,
            ':page_view'    => self::$pageViewPermissionId,
        ];
        $guestRolePageViewPerId = $database->exec($sql, $params)[0]['id'];

        // pages for guest view: signup, login, forgotPassword, passwordRecovering, error
        foreach ($guestPages as $page) {
            $sql = ('INSERT INTO dshb_pages_permissions
                (role_permission, page)
                VALUES (:role_permission, :page)
            ');
            $params = [
                ':role_permission'  => $guestRolePageViewPerId,
                ':page'             => $pages[$page]['id'],
            ];
            $database->exec($sql, $params);
        }

        $guestPagesForms = ['signup', 'login', 'forgotPassword', 'passwordRecovering'];

        $sql = 'SELECT id FROM dshb_roles_permissions WHERE role = :guest AND permission = :page_edit';
        $params = [
            ':guest'        => self::$guestRoleId,
            ':page_edit'    => self::$pageEditPermissionId,
        ];
        $guestRolePageEditPerId = $database->exec($sql, $params)[0]['id'];

        // pages for guest edit: signup, login, forgotPassword, passwordRecovering
        foreach ($guestPagesForms as $page) {
            $sql = ('INSERT INTO dshb_pages_permissions
                (role_permission, page)
                VALUES (:role_permission, :page)
            ');
            $params = [
                ':role_permission'  => $guestRolePageEditPerId,
                ':page'             => $pages[$page]['id'],
            ];
            $database->exec($sql, $params);
        }

        // all pages for superuser
        $sql = 'SELECT id FROM dshb_roles_permissions WHERE role = :superuser';
        $params = [
            ':superuser' => self::$superuserRoleId,
        ];

        $rolePermissions = $database->exec($sql, $params);

        // pages for superuser: all pages with all existing permissions
        foreach ($rolePermissions as $rolePermission) {
            foreach (array_values($pages) as $page) {
                $sql = ('INSERT INTO dshb_pages_permissions
                    (role_permission, page)
                    VALUES (:role_permission, :page)
                ');
                $params = [
                    ':role_permission'  => $rolePermission['id'],
                    ':page'             => $page['id'],
                ];
                $database->exec($sql, $params);
            }
        }

        // all pages for superuser
        $sql = 'SELECT id FROM dshb_roles_permissions WHERE role = :operator';
        $params = [
            ':operator' => self::$operatorRoleId,
        ];

        $rolePermissions = $database->exec($sql, $params);

        // pages for operator: all pages except cron with all existing permissions
        foreach ($rolePermissions as $rolePermission) {
            foreach ($pages as $pageName => $page) {
                if ($pageName === 'cron') {
                    continue;
                }
                $sql = ('INSERT INTO dshb_pages_permissions
                    (role_permission, page)
                    VALUES (:role_permission, :page)
                ');
                $params = [
                    ':role_permission'  => $rolePermission['id'],
                    ':page'             => $page['id'],
                ];

                $database->exec($sql, $params);
            }
        }

        // dshb_operators_roles         stores ids of role + operator pairs
        $queries = [
            self::regularSequence('dshb_operators_roles_id_seq'),
            ('CREATE TABLE dshb_operators_roles (
                id BIGINT NOT NULL DEFAULT nextval(\'dshb_operators_roles_id_seq\'::regclass),
                operator BIGINT NOT NULL,
                role BIGINT NOT NULL
            )'),
            'ALTER SEQUENCE dshb_operators_roles_id_seq OWNED BY dshb_operators_roles.id',
            'ALTER TABLE ONLY dshb_operators_roles ADD CONSTRAINT dshb_operators_roles_id_pkey PRIMARY KEY (id)',
            'CREATE UNIQUE INDEX dshb_operators_roles_operator_role_uidx ON dshb_operators_roles USING btree (operator, role)',
            'CREATE INDEX dshb_operators_roles_operator_idx ON dshb_operators_roles USING btree (operator)',
            'CREATE INDEX dshb_operators_roles_role_idx ON dshb_operators_roles USING btree (role)',
            'ALTER TABLE ONLY dshb_operators_roles ADD CONSTRAINT dshb_operators_roles_operator_fkey FOREIGN KEY (operator) REFERENCES dshb_operators(id) ON UPDATE CASCADE ON DELETE CASCADE',
            'ALTER TABLE ONLY dshb_operators_roles ADD CONSTRAINT dshb_operators_roles_role_fkey FOREIGN KEY (role) REFERENCES dshb_roles(id) ON UPDATE CASCADE ON DELETE CASCADE',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        // assing operator role to all operators
        $sql = ('INSERT INTO dshb_operators_roles (
                operator,
                role
            )
            SELECT
                dshb_operators.id   AS operator,
                :operator          AS role
            FROM
                dshb_operators
        ');

        $params = [':operator' => self::$operatorRoleId];

        $database->exec($sql, $params);

        // assing guest to all operators
        $sql = ('INSERT INTO dshb_operators_roles (
                operator,
                role
            )
            SELECT
                dshb_operators.id   AS operator,
                :guest              AS role
            FROM
                dshb_operators
        ');

        $params = [':guest' => self::$guestRoleId];

        $database->exec($sql, $params);

        $sql = 'ALTER TABLE dshb_operators_rules ADD COLUMN set smallint DEFAULT ' . strval(self::$primaryRulesSetId);
        $database->exec($sql, null);

        $queries = [
            'ALTER TABLE dshb_operators_rules DROP CONSTRAINT dshb_operators_rules_key_rule_uid_key',
            'ALTER TABLE ONLY dshb_operators_rules ADD CONSTRAINT dshb_operators_rules_key_rule_uid_set_key UNIQUE (key, rule_uid, set)',

            self::regularSequence('event_account_score_id_seq'),
            ('CREATE TABLE event_account_score (
                id BIGINT NOT NULL DEFAULT nextval(\'event_account_score_id_seq\'::regclass),
                account BIGINT NOT NULL,
                set smallint NOT NULL,
                score smallint NOT NULL,
                rules jsonb DEFAULT \'[]\'::jsonb,
                key smallint NOT NULL,
                lastseen timestamp without time zone NOT NULL,
                created timestamp without time zone DEFAULT now() NOT NULL
            )'),
            'ALTER SEQUENCE event_account_score_id_seq OWNED BY event_account_score.id',
            'CREATE UNIQUE INDEX event_account_score_account_set_uidx ON event_account_score USING btree (account, set)',
            'CREATE INDEX event_account_score_key_idx ON event_account_score USING btree (key)',
            'CREATE INDEX event_account_score_rules_idx ON event_account_score USING GIN (rules)',
            'ALTER TABLE ONLY event_account_score ADD CONSTRAINT event_account_score_id_pkey PRIMARY KEY (id)',
            'ALTER TABLE ONLY event_account_score ADD CONSTRAINT event_account_score_key_fkey FOREIGN KEY (key) REFERENCES dshb_api(id) ON DELETE CASCADE',
            'CREATE TRIGGER restrict_update BEFORE UPDATE ON event_account_score FOR EACH ROW EXECUTE FUNCTION restrict_update()',

            'ALTER TABLE ONLY event_account_score ADD CONSTRAINT event_account_score_account_key_fkey FOREIGN KEY (account, key) REFERENCES event_account(id, key) ON UPDATE CASCADE ON DELETE CASCADE',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        $params = [
            ':primary_rule_set' => self::$primaryRulesSetId,
        ];
        // copy current score to event_account_score
        $sql = ('INSERT INTO event_account_score (
                account,
                set,
                score,
                rules,
                key,
                lastseen
            )
            SELECT
                event_account.id AS account,
                :primary_rule_set AS set,
                event_account.score,
                event_account.score_details,
                event_account.key,
                event_account.score_updated_at
            FROM event_account
            WHERE event_account.score IS NOT NULL'
        );

        $database->exec($sql, $params);

        $queries = [
            'CREATE INDEX event_account_added_to_review_fraud_null_key_idx ON event_account USING btree (key) WHERE added_to_review IS NULL AND fraud IS NULL',
            'CREATE INDEX event_account_fraud_true_id_idx ON event_account USING btree (id) WHERE fraud IS TRUE',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        $queries = [
            'ALTER TABLE dshb_operators_forgot_password DROP CONSTRAINT dshb_operators_forgot_password_operator_id_fkey',
            'ALTER TABLE dshb_operators_forgot_password ADD CONSTRAINT dshb_operators_forgot_password_operator_id_fkey FOREIGN KEY (operator_id) REFERENCES dshb_operators(id) ON UPDATE CASCADE ON DELETE CASCADE',
        ];

        foreach ($queries as $sql) {
            $database->exec($sql);
        }

        $sql = 'SELECT COUNT(*) AS cnt FROM dshb_operators';

        $operators = $database->exec($sql)[0]['cnt'];

        if ($operators) {
            $params = [
                ':safe_shift'   => 1000000,
            ];

            $sql = 'UPDATE dshb_operators SET id = id + :safe_shift';
            $database->exec($sql, $params);

            $params[':shift'] = self::$reservedOperatorIds;
            $sql = 'UPDATE dshb_operators SET id = id - :safe_shift + :shift';
            $database->exec($sql, $params);

            $sql = ('
                SELECT setval(
                    pg_get_serial_sequence(\'dshb_operators\', \'id\'),
                    (SELECT max(id) FROM dshb_operators),
                    true
            )');

            $database->exec($sql);
        } else {
            $params = [':shift' => self::$reservedOperatorIds];
            $sql = 'SELECT setval(\'dshb_operators_id_seq\', :shift, true)';
            $database->exec($sql, $params);
        }

        $params = [
            ':daemon_id'    => self::$daemonId,
            ':daemon_email' => self::$daemonEmail,
        ];

        $sql = ('
            INSERT INTO dshb_operators
                (id, email)
            VALUES
                (:daemon_id, :daemon_email)
        ');

        $database->exec($sql, $params);

        $params = [
            ':superuser'    => self::$superuserRoleId,
            ':guest'        => self::$guestRoleId,
            ':daemon_id'    => self::$daemonId,
        ];

        // set editor role for owners
        $sql = ('
            INSERT INTO dshb_operators_roles
                (operator, role)
            VALUES
                (:daemon_id, :superuser),
                (:daemon_id, :guest)
        ');

        $database->exec($sql, $params);


        $params = [
            ':guest_id'     => self::$guestId,
            ':guest_email'  => self::$guestEmail,
        ];

        $sql = ('
            INSERT INTO dshb_operators
                (id, email)
            VALUES
                (:guest_id, :guest_email)
        ');
        $database->exec($sql, $params);

        $params = [
            ':role'        => self::$guestRoleId,
            ':guest_id'    => self::$guestId,
        ];

        // set editor role for owners
        $sql = ('
            INSERT INTO dshb_operators_roles
                (operator, role)
            VALUES
                (:guest_id, :role)
        ');

        $database->exec($sql, $params);
    }
}
