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

class ApiKeys {
    public static function getCurrentOperatorApiKeyId(): ?int {
        $key = tirreno('utils')->routes->getCurrentRequestApiKey();

        return $key ? $key->id : null;
    }

    public static function getCurrentOperatorApiKeyString(): ?string {
        $key = tirreno('utils')->routes->getCurrentRequestApiKey();

        return $key ? $key->key : null;
    }

    public static function getCurrentOperatorEnrichmentKeyString(): ?string {
        $key = tirreno('utils')->routes->getCurrentRequestApiKey();

        return $key ? $key->token : null;
    }

    public static function getOperatorApiKeys(int $operatorId): array {
        $apiKeys = tirreno('models')->apiKeys->getKeys($operatorId);

        $isOwner = true;
        if (!$apiKeys) {
            $keyId = tirreno('models')->apiKeyCoOwner->getCoOwnershipKeyId($operatorId);

            if ($keyId) {
                $isOwner = false;
                $apiKeys[] = tirreno('models')->apiKeys->getKeyById($keyId);
            }
        }

        return [$isOwner, $apiKeys];
    }

    public static function getFirstKeyByOperatorId(int $operatorId): ?int {
        $apiKeys = tirreno('models')->apiKeys->getKeys($operatorId);

        if (!$apiKeys) {
            $keyId = tirreno('models')->apiKeyCoOwner->getCoOwnershipKeyId($operatorId);

            if ($keyId) {
                $apiKeys[] = tirreno('models')->apiKeys->getKeyById($keyId);
            }
        }

        return $apiKeys[0]['id'] ?? null;
    }
}
