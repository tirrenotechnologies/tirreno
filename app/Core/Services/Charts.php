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

class Charts extends BaseAggregator {
    protected string $namespace = '\\Tirreno\\Models\\Chart\\%s';
    // skip Base and BaseEventsCount

    protected array $objectsMap = [
        'blacklist'         => 'Blacklist',
        'domains'           => 'Domains',
        'emails'            => 'Emails',
        'events'            => 'Events',
        'fields'            => 'FieldAuditTrails',
        'ips'               => 'Ips',
        'isps'              => 'Isps',
        'logbook'           => 'Logbook',
        'phones'            => 'Phones',
        'resources'         => 'Resources',
        'reviewQueue'       => 'ReviewQueue',
        'rules'             => 'Rules',
        'users'             => 'Users',
        'userAgents'        => 'UserAgents',
        'watchlist'         => 'Watchlist',
        //
        'country'           => 'Country',
        'domain'            => 'Domain',
        'field'             => 'FieldAuditTrail',
        'ip'                => 'Ip',
        'isp'               => 'Isp',
        'resource'          => 'Resource',
        'user'              => 'User',
        'userAgent'         => 'UserAgent',
        //
        'userStats'         => 'SessionStat',
    ];
}
