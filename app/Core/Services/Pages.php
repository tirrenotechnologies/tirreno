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

class Pages extends BaseAggregator {
    protected string $namespace = '\\Tirreno\\Controllers\\Pages\\%s';
    // skip Base and Data
    protected array $objectsMap = [
        'api'               => 'Api',
        'blacklist'         => 'Blacklist',
        'countries'         => 'Countries',
        'country'           => 'Country',
        'devices'           => 'Devices',
        'domain'            => 'Domain',
        'domains'           => 'Domains',
        'emails'            => 'Emails',
        'events'            => 'Events',
        'field'             => 'FieldAudit',
        'fields'            => 'FieldAudits',
        'dashboard'         => 'Home',              //!
        'ip'                => 'Ip',
        'ips'               => 'Ips',
        'isp'               => 'Isp',
        'isps'              => 'Isps',
        'logbook'           => 'Logbook',
        'manualCheck'       => 'ManualCheck',
        'phones'            => 'Phones',
        'resource'          => 'Resource',
        'resources'         => 'Resources',
        'reviewQueue'       => 'ReviewQueue',
        'rules'             => 'Rules',
        'settings'          => 'Settings',
        'user'              => 'User',
        'users'             => 'Users',
        'userAgent'         => 'UserAgent',
        'userAgents'        => 'UserAgents',
        'userDetails'       => 'UserDetails',
        'watchlist'         => 'Watchlist',

        'main'              => 'Main',

        'error'                 => 'Error',
        'forgotPassword'        => 'ForgotPassword',
        'login'                 => 'Login',
        'logout'                => 'Logout',
        'signup'                => 'Signup',
        'passwordRecovering'    => 'PasswordRecovering',
    ];
}
