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

namespace Traits;

trait Db {
    public function connectToDb(bool $keepSessionInDb = true): bool {
        try {
            $db = $this->f3->get('API_DATABASE');

            if (!$db) {
                $url = \Utils\Variables::getDB();

                if ($url === null) {
                    return false;
                }
                $db = $this->getDbConnection($url);

                if ($keepSessionInDb) {
                    new \DB\SQL\Session($db, 'dshb_sessions');
                }

                $this->f3->set('API_DATABASE', $db);
            }

            return true;
        } catch (\Exception $e) {
            error_log('Failed to establish database connection: ' . $e->getMessage());
        }

        return false;
    }

    private function getDbConnection(string $url): ?\DB\SQL {
        $urlComponents = parse_url($url);

        $host = $urlComponents['host'];
        $port = $urlComponents['port'];
        $user = $urlComponents['user'];
        $pass = $urlComponents['pass'];
        $db = ltrim($urlComponents['path'], '/');

        // Include port in DSN if it's set
        $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $db);
        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ];
        try {
            return new \DB\SQL($dsn, $user, $pass, $options);
        } catch (\Exception $e) {
            throw new \Exception('Failed to establish database connection: ' . $e->getMessage());
        }
    }

    private function getOperatorInfoFromF3(): \Models\Operator|false|null {
        return $this->f3->get('CURRENT_USER');
    }

    private function getOperatorInfoFromDb(): \Models\Operator|false|null {
        $model = new \Models\Operator();
        $loggedInOperatorId = $this->f3->get('SESSION.active_user_id');

        return $loggedInOperatorId ? $model->getOperatorById($loggedInOperatorId) : null;
    }

    public function getLoggedInOperator(): \Models\Operator|false|null {
        $testId = $this->f3->get('TEST_API_KEY_ID');
        if ($testId !== null) {
            $keyModel = new \Models\ApiKeys();
            $operatorModel = new \Models\Operator();
            $loggedInOperatorId = $keyModel->getKeyById($testId)->creator;

            return $operatorModel->getOperatorById($loggedInOperatorId);
        }

        $user = $this->getOperatorInfoFromF3();

        if (!$user) {
            $user = $this->getOperatorInfoFromDb();
        }

        return $user;
    }

    public function showForbiddenIfUnlogged(): void {
        if (!boolval($this->getLoggedInOperator())) {
            $this->f3->error(403);
        }
    }

    public function redirectIfUnlogged(string $targetPage = '/'): void {
        if (!boolval($this->getLoggedInOperator())) {
            $this->f3->reroute($targetPage);
        }
    }

    public function redirectIfLogged(): void {
        if (boolval($this->getLoggedInOperator())) {
            $this->f3->reroute('/');
        }
    }
}
