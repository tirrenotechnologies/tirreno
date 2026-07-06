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

namespace Tirreno\Utils;

class Access {
    public static function cleanHost(): void {
        $host = tirreno('utils')->variables->getHostWithProtocol();
        $host = strtolower(parse_url($host, PHP_URL_HOST));

        tirreno('storage')->set('HOST', $host);

        return;
    }

    public static function CSRFTokenValid(array $params): int|false {
        $token = $params['token'] ?? null;
        $csrf = tirreno('session')->get('csrf');

        if (!isset($token) || $token === '' || !isset($csrf) || $csrf === '' || $token !== $csrf) {
            return tirreno('utils')->errorCodes->CSRF_ATTACK_DETECTED;
        }

        return false;
    }

    public static function checkApiKeyAccess(int $keyId, int $operatorId): bool {
        $keyExists = tirreno('models')->apiKeys->existsByKeyAndOperatorId($keyId, $operatorId);

        if ($keyExists) {
            return true;
        }

        $key = tirreno('models')->apiKeyCoOwner->getCoOwnershipKeyId($operatorId);

        return boolval($key);
    }

    public static function checkCurrentOperatorApiKeyAccess(int $keyId): bool {
        $operatorId = self::getCurrentOperatorId();

        return $operatorId && self::checkApiKeyAccess($keyId, $operatorId);
    }

    public static function getCurrentOperatorId(): int {
        return tirreno('utils')->routes->getCurrentRequestOperator()->id;
    }

    public static function getCurrentOperatorApiKeyId(): ?int {
        return tirreno('utils')->routes->getCurrentRequestApiKey()?->id;
    }

    public static function hashPassword(string $password): string {
        $pepper = tirreno('utils')->variables->getPepper();
        $pepperedPassword = hash_hmac('sha256', $password, $pepper);

        return password_hash($pepperedPassword, PASSWORD_DEFAULT);
    }

    public static function verifyPassword(string $unverified, string $password): bool {
        $pepper = tirreno('utils')->variables->getPepper();
        $pepperedPassword = hash_hmac('sha256', $unverified, $pepper);

        return password_verify($pepperedPassword, $password);
    }

    public static function saltHash(string $string): string {
        $iterations = 1000;
        $salt = tirreno('storage')->get('SALT');

        return hash_pbkdf2('sha256', $string, $salt, $iterations, 32);
    }

    public static function pseudoRandString(int $length = 32): string {
        $bytes = random_bytes(intdiv($length, 2));

        return bin2hex($bytes);
    }
}
