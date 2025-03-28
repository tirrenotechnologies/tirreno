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

namespace Controllers\Admin\Rules;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function proceedPostRequest(array $params): array {
        return match ($params['cmd']) {
            'changeThresholdValues' => $this->changeThresholdValues($params),
            default => []
        };
    }

    public function changeThresholdValues(array $params): array {
        $pageParams = [];
        $errorCode = $this->validateThresholdValues($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;

            $model = new \Models\ApiKeys();
            $model->getKeyById($keyId);

            $blacklistThreshold = (int) ($params['blacklist-threshold'] ?? -1);
            $reviewQueueThreshold = (int) ($params['review-queue-threshold'] ?? 0);

            $recalculateReviewQueueCnt = $model->review_queue_threshold !== $reviewQueueThreshold;

            $model->updateBlacklistThreshold($blacklistThreshold);
            $model->updateReviewQueueThreshold($reviewQueueThreshold);

            if ($recalculateReviewQueueCnt) {
                $controller = new \Controllers\Admin\ReviewQueue\Data();
                $controller->getNumberOfNotReviewedUsers($keyId, false, true);
            }

            $pageParams['SUCCESS_MESSAGE'] = $this->f3->get('AdminThresholdValues_update_success_message');
        }

        return $pageParams;
    }

    private function validateThresholdValues(array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $keyId = isset($params['keyId']) ? (int) $params['keyId'] : null;
        if (!$keyId) {
            return \Utils\ErrorCodes::API_KEY_ID_DOESNT_EXIST;
        }

        $blacklistThreshold = (int) ($params['blacklist-threshold'] ?? -1);
        if ($blacklistThreshold < -1 || $blacklistThreshold > 18) {
            return \Utils\ErrorCodes::BLACKLIST_THRESHOLD_DOES_NOT_EXIST;
        }

        $reviewQueueThreshold = (int) ($params['review-queue-threshold'] ?? 0);
        if ($reviewQueueThreshold < 0 || $reviewQueueThreshold > 100) {
            return \Utils\ErrorCodes::REVIEW_QUEUE_THRESHOLD_DOES_NOT_EXIST;
        }

        if ($reviewQueueThreshold <= $blacklistThreshold) {
            return \Utils\ErrorCodes::BLACKLIST_THRESHOLD_EXCEEDS_REVIEW_QUEUE_THRESHOLD;
        }

        return false;
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

        return [$isOwner, $apiKeys];
    }

    public function getRulesForLoggedUser(): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        return $this->getAllRulesByApiKey($apiKey);
    }

    public function saveUserRule(int $ruleId, int $score): void {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Rules();
        $model->updateRule($ruleId, $score, $apiKey);
    }

    public function saveRuleProportion(int $ruleId, float $proportion): void {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Rules();
        $model->updateRuleProportion($ruleId, $proportion, $apiKey);
    }

    public function getRuleProportion(int $totalUsers, int $ruleUsers): float {
        if ($ruleUsers === 0 || $totalUsers === 0) {
            return 0.0;
        }

        $proportion = (float) (100 * $ruleUsers) / (float) $totalUsers;

        // if number is too small make it a bit greater so it will be written in db as 0 < n < 1
        return abs($proportion) < 0.001 ? 0.001 : $proportion;
    }

    public function updateScoreByAccountId(int $accountId, int $apiKey, ?\Models\Rules $rulesModel = null): void {
        $dataController = new \Controllers\Admin\Context\Data();

        if ($rulesModel === null) {
            $rulesModel = new \Models\Rules();
        }

        $this->updateTotalsByAccountIds([$accountId], $apiKey);

        $context = $dataController->getContextByAccountId($accountId, $apiKey);
        $rules = $this->getAllRulesWithOperatorValues($rulesModel, $apiKey);

        [$total, $details] = $this->calculateScore($context, $rules);

        $data = [
            'apiKey'    => $apiKey,
            'score'     => $total,
            'details'   => json_encode($details),
            'accountId' => $accountId,
        ];

        $model = new \Models\ApiKeys();
        $model->getKeyById($apiKey);

        // $blacklistThreshold = $model->blacklist_threshold;
        $blacklistThreshold = -1;
        if ($total <= $blacklistThreshold) {
            (new \Controllers\Admin\User\Data())->addToBlacklistQueue($accountId, true, $apiKey);
        }

        $dataController->updateScoreDetails($data);
    }

    public function calculateScore(array $context, array $rules): array {
        $details = [];

        if (count($context)) {
            $ruler = new Ruler();
            foreach ($rules as $rule) {
                $score = $ruler->calculate($rule, $context);
                if ($score !== -1) {
                    $details[] = [
                        'id' => $rule['id'],
                        'score' => $score,
                    ];
                }
            }
        }

        $total = $this->normalizeScore($details);

        return [$total, $details];
    }

    private function normalizeScore(array $data): int {
        $scores = array_column($data, 'score');
        $totalScore = max(array_sum($scores), 0);

        $filterScores = array_filter($scores, function ($value) {
            return $value > 0;
        });

        $matches = count($filterScores);

        return max((int) (99 - ($totalScore * (pow($matches, 1.1) - $matches + 1))), 0);
    }

    private function suspiciousUsers(Ruler $ruler, array $rule, array $users, array $context): array {
        $suspiciousUsers = [];

        foreach ($users as $user) {
            $record = $context[$user['accountid']] ?? null;
            if ($record !== null && count($record)) {
                $score = $ruler->calculate($rule, $record);
                if ($score > 0) {
                    $user['score'] = $score;
                    $suspiciousUsers[] = $user;
                }
            }
        }

        return $suspiciousUsers;
    }

    public function checkRule(int $ruleId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        $model = new \Models\Users();
        $users = $model->getAllUsersIdsOrdered($apiKey);

        $model = new \Models\Rules();
        $rules = $model->getAll();

        $targetRule = [];
        if (isset($rules[$ruleId])) {
            $targetRule = $rules[$ruleId];
            $targetRule['value'] = 1;
        }

        $suspiciousUsers = [];
        $accountIds = [];
        $preparedContext = [];
        $ruler = new Ruler();

        foreach (array_chunk($users, \Utils\Variables::getRuleUsersBatchSize()) as $usersBatch) {
            $accountIds = array_column($usersBatch, 'accountid');
            $preparedContext = $this->getContextByAccountIds($accountIds, $apiKey);
            $suspiciousUsers = array_merge($suspiciousUsers, $this->suspiciousUsers($ruler, $targetRule, $usersBatch, $preparedContext));
        }

        return [count($users), $suspiciousUsers];
    }

    public function getContextByAccountIds(array $accountsIds, $apiKey): array {
        $dataController = new \Controllers\Admin\Context\Data();

        return $dataController->getContextByAccountIds($accountsIds, $apiKey);
    }

    public function updateTotalsByAccountIds(array $accountsIds, int $apiKey): void {
        foreach (\Utils\Constants::get('RULES_TOTALS_MODELS') as $model) {
            (new $model())->updateTotalsByAccountIds($accountsIds, $apiKey);
        }
    }

    private function getAllRulesWithOperatorValues(\Models\Rules $rulesModel, int $apiKey): array {
        $model = new \Models\ApiKeys();
        $skipAttributes = $model->getSkipEnrichingAttributes($apiKey);
        $rules = $rulesModel->getAllRulesWithOperatorValues($apiKey);

        return \Utils\Rules::filterRulesByAttributes($rules, $skipAttributes);
    }

    public function getAllRulesByApiKey(int $apiKey): array {
        $model = new \Models\ApiKeys();
        $skipAttributes = $model->getSkipEnrichingAttributes($apiKey);

        $model = new \Models\Rules();
        $results = $model->getAllByOperator($apiKey);

        $results = \Utils\Rules::filterRulesByAttributes($results, $skipAttributes);

        for ($i = 0; $i < count($results); ++$i) {
            $results[$i]['type'] = \Utils\Rules::getRuleTypeByUid($results[$i]['uid']);
        }

        usort($results, static function ($a, $b): int {
            return $a['uid'] <=> $b['uid'];
        });

        return $results;
    }
}
