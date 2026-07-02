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

class Entities extends BaseAggregator {
    protected string $namespace = '\\Tirreno\\Entities\\%s';

    // skip Multiple, Single, Base, BaseEmpty
    protected array $objectsMap = [
        'apiKey'        => 'ApiKey',
        'country'       => 'Country',
        'countries'     => 'Countries',
        'device'        => 'Device',
        'devices'       => 'Devices',
        'email'         => 'Email',
        'emails'        => 'Emails',
        'emptyEmail'    => 'EmptyEmail',
        'event'         => 'Event',
        'events'        => 'Events',
        'httpRequest'   => 'HttpRequest',
        'httpResponse'  => 'HttpResponse',
        'ip'            => 'Ip',
        'ips'           => 'Ips',
        'isp'           => 'Isp',
        'isps'          => 'Isps',
        'logbook'       => 'Logbook',
        'operator'      => 'Operator',
        'payload'       => 'Payload',
        'payloads'      => 'Payloads',
        'phone'         => 'Phone',
        'phones'        => 'Phones',
        'emptyPhone'    => 'EmptyPhone',
        'query'         => 'Query',
        'queries'       => 'Queries',
        'emptyQuery'    => 'EmptyQuery',
        'referer'       => 'Referer',
        'referers'      => 'Referers',
        'emptyReferer'  => 'EmptyReferer',
        'resource'      => 'Resources',
        'rules'         => 'Rules',
        'rule'          => 'Rule',
        'session'       => 'Session',
        'sessions'      => 'Sessions',
        'user'          => 'User',
        'users'         => 'Users',
    ];

    protected function createObject(string $name, string $className, bool $getFullClass): object {
        return new \Tirreno\Core\Services\StaticProxy($this->getClassName($className, $getFullClass));
    }
}
