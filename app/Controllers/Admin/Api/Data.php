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

namespace Controllers\Admin\Api;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    protected $ENRICHED_ATTRIBUTES = [];

    public function __construct() {
        parent::__construct();

        $this->ENRICHED_ATTRIBUTES = array_keys(\Utils\Constants::ENRICHING_ATTRIBUTES);
    }

    public function proceedPostRequest(array $params): array {
        $cmd = $params['cmd'] ?? '';

        return match ($cmd) {
            'resetKey'                      => $this->resetApiKey($params),
            'updateApiUsage'                => $this->updateApiUsage($params),
            'enrichAll'                     => $this->enrichAll($params),
            default => []
        };
    }

    public function getUsageStats(int $operatorId): array {
        $model = new \Models\ApiKeys();
        $apiKeys = $model->getKeys($operatorId);

        $isOwner = true;
        if (!$apiKeys) {
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            if ($coOwnerModel->loaded()) {
                $isOwner = false;
                $apiKeys[] = $model->getKeyById($coOwnerModel->api);
            }
        }

        if (!$isOwner) {
            return ['data' => []];
        }

        $resultKeys = [];

        foreach ($apiKeys as $key) {
            $subscriptionStats = [];
            if ($key->token !== null) {
                [$code, $response, $error] = $this->getSubscriptionStats($key->token);
                $subscriptionStats = strlen($error) > 0 || $code > 201 ? [] : $response;
            }

            $remaining = $subscriptionStats['remaining'] ?? null;
            $total = $subscriptionStats['total'] ?? null;
            $used = $remaining !== null && $total !== null ? $total - $remaining : null;

            $resultKeys[] = [
                'id'                        => $key->id,
                'key'                       => $key->key,
                'apiToken'                  => $key->token ?? null,
                'sub_status'                => $subscriptionStats['status'] ?? null,
                'sub_calls_left'            => $remaining,
                'sub_calls_used'            => $used,
                'sub_calls_limit'           => $total,
                'sub_next_billed'           => $subscriptionStats['next_billed_at'] ?? null,
                'sub_update_url'            => $subscriptionStats['update_url'] ?? null,
                'sub_plan_id'               => $subscriptionStats['current_subscription_plan']['sub_id'] ?? null,
                'sub_plan_api_calls'        => $subscriptionStats['current_subscription_plan']['api_calls'] ?? null,
                //'all_subscription_plans'    => $subscriptionStats['all_subscription_plans'] ?? null,
            ];
        }

        return ['data' => $resultKeys];
    }

    public function getOperatorApiKeys(int $operatorId): array {
        $model = new \Models\ApiKeys();
        $apiKeys = $model->getKeys($operatorId);

        $isOwner = true;
        if (!$apiKeys) {
            $coOwnerModel = new \Models\ApiKeyCoOwner();
            $coOwnerModel->getCoOwnership($operatorId);

            if ($coOwnerModel->loaded()) {
                $isOwner = false;
                $apiKeys[] = $model->getKeyById($coOwnerModel->api);
            }
        }

        $resultKeys = [];

        foreach ($apiKeys as $key) {
            $resultKeys[] = [
                'id'                        => $key->id,
                'key'                       => $key->key,
                'created_at'                => $key->created_at,
                'skip_enriching_attributes' => $key->skip_enriching_attributes,
                'enrichedAttributes'        => $this->getEnrichedAttributes($key),
                'retention_policy'          => $key->retention_policy,
                'skip_blacklist_sync'       => $key->skip_blacklist_sync,
                'apiToken'                  => $key->token ?? null,
            ];
        }

        return [$isOwner, $resultKeys];
    }

    private function getSubscriptionStats(string $token): array {
        $api = \Utils\Variables::getEnrichtmentApi();

        $options = [
            'method' => 'GET',
            'header' => [
                sprintf('Authorization: Bearer %s', $token),
            ],
        ];

        /** @var array{request: array<string>, body: string, headers: array<string>, engine: string, cached: bool, error: string} $result */
        $result = \Web::instance()->request(
            url: sprintf('%s/usage-stats', $api),
            options: $options,
        );

        $matches = [];
        preg_match('/^HTTP\/(\d+)(?:\.\d)? (\d{3})/', $result['headers'][0], $matches);
        $statusCode = (int) ($matches[2] ?? 0);

        $errorMessage = $result['error'];
        $jsonResponse = json_decode($result['body'], true);

        return [$statusCode, $jsonResponse, $errorMessage];
    }

    public function resetApiKey(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateResetApiKey($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;

            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);
            $model->resetKey($keyId, $model->creator);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_reset_success_message');
        }

        return $pageParams;
    }

    public function enrichAll(array $data): array {
        $pageParams = [];
        $errorCode = $this->validateEnrichAll($data);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $operatorId = $data['id'];
            $apiKey = $this->getCurrentOperatorApiKeyId();

            $model = new \Models\Users();
            $accountsForEnrichment = $model->notCheckedUsers($apiKey);

            $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Enrichment);
            $accountOperationQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

            $accountOperationQueueModel->addBatch($accountsForEnrichment);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_manual_enrichment_success_message');
        }

        return $pageParams;
    }

    public function validateEnrichAll(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        return false;
    }

    public function validateResetApiKey(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;
        if (!$keyId) {
            return \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST;
        }

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;

        if (!$this->validateApiKeyAccess($keyId, $operatorId)) {
            return \Utils\ErrorCodes::API_KEY_WAS_CREATED_FOR_ANOTHER_USER;
        }

        return false;
    }

    private function validateApiKeyAccess(int $keyId, int $operatorId): bool {
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

    public function getEnrichedAttributes(\Models\ApiKeys $key): array {
        $enrichedAttributes = [];
        $skipAttributes = \json_decode($key->skip_enriching_attributes);
        foreach ($this->ENRICHED_ATTRIBUTES as $attribute) {
            $enrichedAttributes[$attribute] = !\in_array($attribute, $skipAttributes);
        }

        return $enrichedAttributes;
    }

    public function updateApiUsage(array $params): array {
        $errorCode = $this->validateUpdateApiUsage($params);
        $pageParams = [];

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;
            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);

            if ($params['apiToken'] !== null) {
                $apiToken = trim($params['apiToken']);
                [$code, , $error] = $this->getSubscriptionStats($apiToken);
                if (strlen($error) > 0 || $code > 201) {
                    $pageParams['ERROR_CODE'] = \Utils\ErrorCodes::SUBSCRIPTION_KEY_INVALID_UPDATE;
                    return $pageParams;
                }
                $model->updateInternalToken($apiToken);
            }

            $enrichedAttributes = $params['enrichedAttributes'] ?? [];
            $skipEnrichingAttributes = \array_diff($this->ENRICHED_ATTRIBUTES, \array_keys($enrichedAttributes));
            $model->updateSkipEnrichingAttributes($skipEnrichingAttributes);

            $skipBlacklistSynchronisation = !isset($params['exchangeBlacklist']);
            $model->updateSkipBlacklistSynchronisation($skipBlacklistSynchronisation);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_data_enrichment_success_message');
        }

        return $pageParams;
    }

    public function validateUpdateApiUsage(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;
        if (!$keyId) {
            return \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST;
        }

        $currentOperator = $this->f3->get('CURRENT_USER');
        $operatorId = $currentOperator->id;
        if (!$this->validateApiKeyAccess($keyId, $operatorId)) {
            return \Utils\ErrorCodes::API_KEY_WAS_CREATED_FOR_ANOTHER_USER;
        }

        $enrichedAttributes = $params['enrichedAttributes'] ?? [];
        $unknownAttributes = \array_diff(\array_keys($enrichedAttributes), $this->ENRICHED_ATTRIBUTES);
        if ($unknownAttributes) {
            return \Utils\ErrorCodes::UNKNOWN_ENRICHMENT_ATTRIBUTES;
        }

        return false;
    }

    public function getNotCheckedEntitiesForLoggedUser(): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $controller = new \Controllers\Admin\Enrichment\Data();

        return $controller->getNotCheckedExists($apiKey);
    }

    public function getScheduledForEnrichment(): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Enrichment);
        $accountOperationQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

        // do not use isInQueue() to prevent true on failed state
        return $accountOperationQueueModel->actionIsInQueueProcessing($apiKey);
    }
}
