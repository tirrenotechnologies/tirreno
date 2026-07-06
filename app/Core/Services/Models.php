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

namespace Tirreno\Core\Services;

class Models extends BaseAggregator {
    protected string $namespace = '\\Tirreno\\Models\\%s';

    // skip Base and nested dirs Chart, Context, Enrichment, Grid, Search, TopTen, UserDetails, Query
    protected array $objectsMap = [
        'api'               => 'Api',
        'apiKeyCoOwner'     => 'ApiKeyCoOwner',
        'apiKeys'           => 'ApiKeys',
        'blacklistItems'    => 'BlacklistItems',
        'country'           => 'Country',
        'cursor'            => 'Cursor',
        'dashboard'         => 'Dashboard',
        'device'            => 'Device',
        'domain'            => 'Domain',
        'email'             => 'Email',
        'event'             => 'Event',
        'eventType'         => 'EventType',
        'events'            => 'Events',
        'fieldAudit'        => 'FieldAudit',
        'fieldAuditTrail'   => 'FieldAuditTrail',
        'forgotPassword'    => 'ForgotPassword',
        'ip'                => 'Ip',
        'ips'               => 'Ips',
        'isp'               => 'Isp',
        'log'               => 'Log',
        'logbook'           => 'Logbook',
        'manualCheck'       => 'ManualCheckHistory',        // !
        'map'               => 'Map',
        'message'           => 'Message',
        'notification'      => 'NotificationPreferences',   // !
        'operator'          => 'Operator',
        'operatorsRoles'    => 'OperatorsRoles',
        'operatorsRules'    => 'OperatorsRules',
        'pages'             => 'Pages',
        'pagesPermissions'  => 'PagesPermissions',
        'payload'           => 'Payload',
        'permissions'       => 'Permissions',
        'phone'             => 'Phone',
        'queue'             => 'Queue',
        'resource'          => 'Resource',
        'retentionPolicies' => 'RetentionPolicies',
        'reviewQueue'       => 'ReviewQueue',
        'roles'             => 'Roles',
        'rolesPermissions'  => 'RolesPermissions',
        'rules'             => 'Rules',
        'session'           => 'Session',
        'sessionStat'       => 'SessionStat',
        'updates'           => 'Updates',
        'user'              => 'User',
        'users'             => 'Users',
        'userAgent'         => 'UserAgent',
        'userScore'         => 'UserScore',
        'userStat'          => 'UsersStat',
        'userDetails'       => 'UserDetails',
        //'watchlist'         => 'Watchlist',
    ];
}
