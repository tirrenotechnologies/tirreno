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

trait ApiKeys {
    public function getCurrentOperatorApiKeyId(): ?int {
        $key = $this->getCurrentOperatorApiKeyObject();

        return $key ? $key->id : null;
    }

    public function getCurrentOperatorApiKeyString(): ?string {
        $key = $this->getCurrentOperatorApiKeyObject();

        return $key ? $key->key : null;
    }

    public function getCurrentOperatorEnrichmentKeyString(): ?string {
        $key = $this->getCurrentOperatorApiKeyObject();

        return $key ? $key->token : null;
    }

    // returns \Models\ApiKeys; in test mode returns object
    protected function getCurrentOperatorApiKeyObject(): object|null {
        $currentOperator = $this->f3->get('CURRENT_USER');

        if (!$currentOperator) {
            return null;
        }

        $model = new \Models\ApiKeys();

        //This key specified in the local configuration file and will not applied to the production environment
        $testId = $this->f3->get('TEST_API_KEY_ID');
        if (isset($testId) && $testId !== '') {
            return (object) [
                'id' => $testId,
                'key' => $model->getKeyById($testId)->key,
                'skip_blacklist_sync' => true,
                'token' => $model->getKeyById($testId)->token,
            ];
        }

        $operatorId = $currentOperator->id;
        $key = $model->getKey($operatorId);

        if (!$key) { // Check if operator is co-owner of another API key when it has no own API key.
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            if ($coOwnerModel->loaded()) {
                $key = $model->getKeyById($coOwnerModel->api);
            }
        }

        return $key;
    }
}
