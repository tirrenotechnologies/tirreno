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

namespace Tirreno\Core;

class Container {
    private function __construct() {
    }

    private static array $services = [
        'session'       => \Tirreno\Core\Services\Session::class,
        'request'       => \Tirreno\Core\Services\Request::class,
        'response'      => \Tirreno\Core\Services\Response::class,

        'sysop'         => \Tirreno\Core\Services\Sysop::class,

        'storage'       => \Tirreno\Core\Services\Storage::class,
        'page'          => \Tirreno\Core\Services\Page::class,

        'helpers'       => \Tirreno\Core\Services\Helpers::class,

        'db'            => \Tirreno\Core\Services\Db::class,
        'log'           => \Tirreno\Core\Services\Log::class,
        'rule'          => \Tirreno\Core\Services\Rule::class,
        'user'          => \Tirreno\Core\Services\User::class,
        'ip'            => \Tirreno\Core\Services\Ip::class,
        'resource'      => \Tirreno\Core\Services\Resource::class,

        'rules'         => \Tirreno\Core\Services\Rules::class,
        'users'         => \Tirreno\Core\Services\Users::class,
        'ips'           => \Tirreno\Core\Services\Ips::class,
        'resources'     => \Tirreno\Core\Services\Resources::class,

        'router'        => \Tirreno\Core\Services\Router::class,

        'controllers'   => \Tirreno\Core\Services\Controllers::class,
        'pages'         => \Tirreno\Core\Services\Pages::class,
        'models'        => \Tirreno\Core\Services\Models::class,
        'utils'         => \Tirreno\Core\Services\Utils::class,
        'grids'         => \Tirreno\Core\Services\Grids::class,
        'charts'        => \Tirreno\Core\Services\Charts::class,
        'assets'        => \Tirreno\Core\Services\Assets::class,
        'entities'      => \Tirreno\Core\Services\Entities::class,
        'queries'       => \Tirreno\Core\Services\Queries::class,
    ];

    private static array $renewable = [
        'users',
        'ips',
        'resources'
    ];

    private static array $instances = [];

    public static function resolve(string $name): object {
        if (!isset(self::$services[$name])) {
            throw new \Exception('Validation failed');
        }

        if (in_array($name, self::$renewable)) {
            return new self::$services[$name]();
        }

        if (!isset(self::$instances[$name])) {
            if ($name === 'router') {
                self::$instances[$name] = tirreno('utils')->router->get();
            } else {
                self::$instances[$name] = new self::$services[$name]();
            }
        }

        return self::$instances[$name];
    }
}
