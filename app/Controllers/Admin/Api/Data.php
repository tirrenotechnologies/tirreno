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

namespace Controllers\Admin\Api;

class Data extends \Controllers\Admin\Base\Data {
    protected $ENRICHED_ATTRIBUTES = [];

    public function __construct() {
        parent::__construct();

        $this->ENRICHED_ATTRIBUTES = array_keys(\Utils\Constants::get('ENRICHING_ATTRIBUTES'));
    }

    public function proceedPostRequest(): array {
        return match (\Utils\Conversion::getStringRequestParam('cmd')) {
            'resetKey'          => $this->resetApiKey(),
            'updateApiUsage'    => $this->updateApiUsage(),
            'enrichAll'         => $this->enrichAll(),
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

    public function getOperatorApiKeysDetails(int $operatorId): array {
        [$isOwner, $apiKeys] = \Utils\ApiKeys::getOperatorApiKeys($operatorId);

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
        $response = \Utils\Network::sendApiRequest(null, '/usage-stats', 'GET', $token);
        $code = $response['code'];
        $result = $response['data'];

        $jsonResponse = is_array($result) ? $result : [];
        $statusCode = $code ?? 0;

        $errorMessage = $response['error'] ?? '';

        return [$statusCode, $jsonResponse, $errorMessage];
    }

    public function resetApiKey(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token', 'keyId']);
        $errorCode = \Utils\Validators::validateResetApiKey($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = \Utils\Conversion::getIntRequestParam('keyId');

            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);
            $model->resetKey($keyId, $model->creator);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_reset_success_message');
        }

        return $pageParams;
    }

    public function enrichAll(): array {
        $pageParams = [];
        $params = $this->extractRequestParams(['token']);
        $errorCode = \Utils\Validators::validateEnrichAll($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();

            $model = new \Models\Users();
            $accountsForEnrichment = $model->notCheckedUsers($apiKey);

            (new \Models\Queue())->addBatchIds($accountsForEnrichment, \Utils\Constants::get('ENRICHMENT_QUEUE_ACTION_TYPE'), $apiKey);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_manual_enrichment_success_message');
        }

        return $pageParams;
    }

    public function getEnrichedAttributes(\Models\ApiKeys $key): array {
        $enrichedAttributes = [];
        $skipAttributes = \json_decode($key->skip_enriching_attributes);
        foreach ($this->ENRICHED_ATTRIBUTES as $attribute) {
            $enrichedAttributes[$attribute] = !\in_array($attribute, $skipAttributes);
        }

        return $enrichedAttributes;
    }

    public function updateApiUsage(): array {
        $pageParams = [];
        // apiToken, exchangeBlacklist optional
        $params = $this->extractRequestParams(['token', 'keyId', 'enrichedAttributes']);
        $errorCode = \Utils\Validators::validateUpdateApiUsage($params, $this->ENRICHED_ATTRIBUTES);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = \Utils\Conversion::getIntRequestParam('keyId');

            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);

            $apiToken = \Utils\Conversion::getStringRequestParam('apiToken', true);

            if ($apiToken !== null) {
                $apiToken = trim($apiToken);
                [$code, , $error] = $this->getSubscriptionStats($apiToken);
                if (strlen($error) > 0 || $code > 201) {
                    $pageParams['ERROR_CODE'] = \Utils\ErrorCodes::SUBSCRIPTION_KEY_INVALID_UPDATE;
                    return $pageParams;
                }
                $model->updateInternalToken($apiToken);
            }

            $enrichedAttributes = \Utils\Conversion::getArrayRequestParam('enrichedAttributes');
            $skipEnrichingAttributes = \array_diff($this->ENRICHED_ATTRIBUTES, \array_keys($enrichedAttributes));
            $model->updateSkipEnrichingAttributes($skipEnrichingAttributes);

            $skipBlacklistSync = !\Utils\Conversion::getStringRequestParam('exchangeBlacklist');
            $model->updateSkipBlacklistSynchronisation($skipBlacklistSync);

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminApi_data_enrichment_success_message');
        }

        return $pageParams;
    }

    public function getNotCheckedEntitiesForLoggedUser(): bool {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $controller = new \Controllers\Admin\Enrichment\Data();

        return $controller->getNotCheckedExists($apiKey);
    }

    public function getScheduledForEnrichment(): bool {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $model = new \Models\Queue();

        // do not use isInQueue() to prevent true on failed state
        return $model->actionIsInQueueProcessing(\Utils\Constants::get('ENRICHMENT_QUEUE_ACTION_TYPE'), $apiKey);
    }
}
