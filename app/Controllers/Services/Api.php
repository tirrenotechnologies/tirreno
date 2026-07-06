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

namespace Tirreno\Controllers\Services;

class Api extends \Tirreno\Controllers\Services\Base {
    protected array $ENRICHED_ATTRIBUTES = [];

    public function __construct() {
        parent::__construct();

        $this->ENRICHED_ATTRIBUTES = array_keys(tirreno('utils')->constants->ENRICHING_ATTRIBUTES);
    }

    public function getUsageStats(int $operatorId): array {
        $apiKeys = tirreno('models')->apiKeys->getKeys($operatorId);

        $isOwner = true;
        if (!$apiKeys) {
            $key = tirreno('models')->apiKeyCoOwner->getCoOwnershipKeyId($operatorId);

            if ($key) {
                $isOwner = false;
                $apiKeys[] = tirreno('models')->apiKeys->getKeyById($key);
            }
        }

        if (!$isOwner) {
            return ['data' => []];
        }

        $resultKeys = [];

        foreach ($apiKeys as $key) {
            $subscriptionStats = [];
            if ($key['token'] !== null) {
                [$code, $response, $error] = $this->getSubscriptionStats($key['token'], $key['id']);
                $subscriptionStats = strlen($error) > 0 || $code > 201 ? [] : $response;
            }

            $remaining = $subscriptionStats['remaining'] ?? null;
            $total = $subscriptionStats['total'] ?? null;
            $used = $remaining !== null && $total !== null ? $total - $remaining : null;

            $resultKeys[] = [
                'id'                        => $key['id'],
                'tokenPresent'              => boolval($key['token']),
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

    public function getOperatorApiKeysDetails(int $operatorId): array {
        [$isOwner, $apiKeys] = tirreno('utils')->apiKeys->getOperatorApiKeys($operatorId);

        $resultKeys = [];

        foreach ($apiKeys as $key) {
            $resultKeys[] = [
                'id'                        => $key['id'],
                'key'                       => $key['key'],
                'created_at'                => $key['created_at'],
                'skip_enriching_attributes' => $key['skip_enriching_attributes'],
                'enrichedAttributes'        => $this->getEnrichedAttributes($key['skip_enriching_attributes']),
                'retention_policy'          => $key['retention_policy'],
                'skip_blacklist_sync'       => $key['skip_blacklist_sync'],
                'apiToken'                  => $key['token'],
            ];
        }

        return [$isOwner, $resultKeys];
    }

    private function getSubscriptionStats(string $token, int $apiKey): array {
        $response = tirreno('utils')->network->sendApiRequest(null, '/usage-stats', 'GET', $token, $apiKey);
        $code = $response->code;
        $result = $response->body;

        $statusCode = $code ?? 0;
        $errorMessage = $response->error ?? '';

        return [$statusCode, $result, $errorMessage];
    }

    public function resetApiKey(int $apiKey): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'keyId']);
        // TODO: valid only for owners?
        $errorCode = tirreno('utils')->validators->validateResetApiKey($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = tirreno('utils')->conversion->getIntRequestParam('keyId');

            $currentOperator = tirreno('utils')->routes->getCurrentRequestOperator();
            $operatorId = $currentOperator->id;

            tirreno('models')->apiKeys->resetKey($keyId, $operatorId);

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('api_reset_success_message');
        }

        return $pageParams;
    }

    public function enrichAll(int $apiKey): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token']);
        $enrichmentKey = tirreno('utils')->apiKeys->getCurrentOperatorEnrichmentKeyString();
        $errorCode = tirreno('utils')->validators->validateEnrichAll($params, $enrichmentKey);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $accountsToEnrich = tirreno('models')->users->notCheckedUsers($apiKey);

            tirreno('models')->queue->addBatchIds($accountsToEnrich, tirreno('utils')->constants->ENRICHMENT_QUEUE_ACTION_TYPE, $apiKey);

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('api_manual_enrichment_success_message');
        }

        return $pageParams;
    }

    private function getEnrichedAttributes(string $attributes): array {
        $enrichedAttributes = [];
        $skipAttributes = json_decode($attributes);
        foreach ($this->ENRICHED_ATTRIBUTES as $attribute) {
            $enrichedAttributes[$attribute] = !in_array($attribute, $skipAttributes);
        }

        return $enrichedAttributes;
    }

    public function updateApiUsage(int $apiKey): array {
        $pageParams = [];
        // apiToken, exchangeBlacklist optional
        $params = tirreno('utils')->render->extractRequestParams(['token', 'keyId', 'enrichedAttributes']);
        $errorCode = tirreno('utils')->validators->validateUpdateApiUsage($params, $this->ENRICHED_ATTRIBUTES);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = tirreno('utils')->conversion->getIntRequestParam('keyId');

            tirreno('models')->apiKeys->getKeyById($keyId);

            $apiToken = tirreno('utils')->conversion->getStringRequestParam('apiToken', true);

            if ($apiToken !== null) {
                $apiToken = trim($apiToken);
                [$code, , $error] = $this->getSubscriptionStats($apiToken, $keyId);
                if (strlen($error) > 0 || $code > 201) {
                    $pageParams['ERROR_CODE'] = tirreno('utils')->errorCodes->SUBSCRIPTION_KEY_INVALID_UPDATE;
                    return $pageParams;
                }
                tirreno('models')->apiKeys->updateInternalToken($apiToken, $keyId);
            }

            $enrichedAttributes = tirreno('utils')->conversion->getDictionaryRequestParam('enrichedAttributes');
            $skipEnrichingAttr = array_diff($this->ENRICHED_ATTRIBUTES, array_keys($enrichedAttributes));
            tirreno('models')->apiKeys->updateSkipEnrichingAttributes($skipEnrichingAttr, $keyId);

            $skipBlacklistSync = !tirreno('utils')->conversion->getStringRequestParam('exchangeBlacklist');
            tirreno('models')->apiKeys->updateSkipBlacklistSynchronisation($skipBlacklistSync, $keyId);

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('api_data_enrichment_success_message');
        }

        return $pageParams;
    }

    public function getNotCheckedEntities(int $apiKey): bool {
        return tirreno('controllers')->enrichment->getNotCheckedExists($apiKey);
    }

    public function getNotCheckedEntitiesCount(int $apiKey): array {
        return tirreno('controllers')->enrichment->getNotCheckedEntitiesCount($apiKey);
    }

    public function getScheduledForEnrichment(int $apiKey): bool {
        // do not use isInQueue() to prevent true on failed state
        return tirreno('models')->queue->actionIsInQueueProcessing(tirreno('utils')->constants->ENRICHMENT_QUEUE_ACTION_TYPE, $apiKey);
    }
}
