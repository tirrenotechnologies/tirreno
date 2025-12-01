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

namespace Utils;

class Database {
    private static function getF3(): \Base {
        return \Base::instance();
    }

    public static function getDb(): \DB\SQL|false|null {
        return self::getF3()->get('APP_DATABASE');
    }

    public static function setDb(\DB\SQL|false|null $database): void {
        self::getF3()->set('APP_DATABASE', $database);
    }

    public static function initConnect(bool $keepSession = true): bool {
        try {
            $database = self::getDb();

            if (!$database) {
                $url = \Utils\Variables::getDB();

                if ($url === null) {
                    return false;
                }

                self::setDb(self::getDbConnect($url));

                if ($keepSession) {
                    new \DB\SQL\Session(self::getDb(), 'dshb_sessions');
                }
            }

            return true;
        } catch (\Exception $e) {
            self::getF3()->error(503);
        }

        return false;
    }

    private static function getDbConnect(string $url): ?\DB\SQL {
        $parts = parse_url($url);

        if (!is_array($parts)) {
            throw new \InvalidArgumentException('Invalid DSN format');
        }

        //$schm = $parts['scheme'] ?? '';
        $host = $parts['host'] ?? '';
        $port = $parts['port'] ?? 5432;
        $user = $parts['user'] ?? '';
        $pass = $parts['pass'] ?? '';
        $path = $parts['path'] ?? '';

        if (!$host || !$host || !$user || !$pass || !$path) {
            throw new \InvalidArgumentException('Invalid DSN format');
        }

        $database = ltrim($path, '/');

        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $database);
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];

        try {
            return new \DB\SQL($dsn, $user, $pass, $options);
        } catch (\Exception $e) {
            throw new \Exception('Failed to establish database connection: ' . $e->getMessage());
        }
    }
}
