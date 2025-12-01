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

class Access {
    public static function cleanHost() {
        $f3 = \Base::instance();

        if (PHP_SAPI === 'cli') {
            $f3->set('HOST', \Utils\Variables::getHost());

            return;
        }

        $httpHosts = \Utils\Variables::getHosts();

        $port = \Utils\Conversion::intValCheckEmpty($_SERVER['SERVER_PORT'] ?? 80, 80);
        $port = ($port === 80 || $port === 443 ? '' : ":$port");
        $key = false;

        if (isset($_SERVER['SERVER_NAME'])) {
            $key = array_search(strtolower($_SERVER['SERVER_NAME']) . $port, $httpHosts, true);
        }
        if ($key === false && isset($_SERVER['HTTP_HOST'])) {
            $key = array_search(strtolower($_SERVER['HTTP_HOST']), $httpHosts, true);
        }

        // store HOST without port for f3
        if ($key !== false) {
            $parts = explode(':', $httpHosts[$key]);
            $cnt = count($parts);
            if ($cnt > 1 && ctype_digit($parts[$cnt - 1])) {
                array_pop($parts);
            }
            $f3->set('HOST', implode(':', $parts));
        } else {
            //$f3->set('HOST', explode(':', $httpHosts[0])[0]);
            if (count($httpHosts) > 1 || $httpHosts[0] !== 'localhost') {
                $f3->error(400);
            }
        }
    }

    public static function CSRFTokenValid(array $params, \Base $f3): int|false {
        $token = $params['token'] ?? null;
        $csrf = $f3->get('SESSION.csrf');

        if (!isset($token) || $token === '' || !isset($csrf) || $csrf === '' || $token !== $csrf) {
            return \Utils\ErrorCodes::CSRF_ATTACK_DETECTED;
        }

        return false;
    }

    public static function checkApiKeyAccess(int $keyId, int $operatorId): bool {
        $model = new \Models\ApiKeys();
        $model->getByKeyAndOperatorId($keyId, $operatorId);

        if (!$model->loaded()) {
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            if (!$coOwnerModel->loaded()) {
                return false;
            }
        }

        return true;
    }

    public static function checkCurrentOperatorApiKeyAccess(int $keyId): bool {
        $operatorId = self::getCurrentOperatorId();

        return $operatorId && self::checkApiKeyAccess($keyId, $operatorId);
    }

    public static function getCurrentOperatorId(): ?int {
        return \Utils\Routes::getCurrentRequestOperator()?->id;
    }

    public static function getCurrentOperatorApiKeyId(): ?int {
        $operatorId = self::getCurrentOperatorId();

        if (!$operatorId) {
            return null;
        }

        $model = new \Models\ApiKeys();
        $key = $model->getKey($operatorId);

        if (!$key) { // Check if operator is co-owner of another API key when it has no own API key.
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            if ($coOwnerModel->loaded()) {
                $key = $model->getKeyById($coOwnerModel->api);
            }
        }

        return $key ? $key->id : null;
    }
}
