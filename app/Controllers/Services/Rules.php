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

class Rules extends \Tirreno\Controllers\Services\Base {
    private object $contextController;
    private object $userController;
    private \Tirreno\Models\OperatorsRules $rulesModel;

    private array $totalModels;
    private array $rulesMap;

    public function refreshRules(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token']);
        $errorCode = tirreno('utils')->validators->validateRefreshRules($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $updateStats = $this->updateRules(true);

            $iterates           = $updateStats['iterates'];
            $oldMissingCnt      = $updateStats['oldMissingCnt'];
            $newMissingRules    = $updateStats['newMissingRules'];

            //$successCnt = count($iterates[5]) + count($iterates[3]);
            //$warningCnt = count($iterates[4]) + count($iterates[2]);

            $newValidCnt    = count($iterates[5]);
            $newInvalidCnt  = count($iterates[4]);
            $updValidCnt    = count($iterates[3]);
            $updInvalidCnt  = count($iterates[2]);
            $missingCnt     = count($newMissingRules);

            $messages = [];

            $messages[] = $this->getStatusNotification($newValidCnt, 'Added %s rule%s: %s', $iterates[5]);
            $messages[] = $this->getStatusNotification($updValidCnt, 'Updated %s rule%s: %s', $iterates[3]);

            $msg = join(";\n", array_filter($messages));

            if ($msg) {
                $pageParams['SUCCESS_MESSAGE'] = $msg;
            }

            $messages = [];

            $messages[] = $this->getStatusNotification($newInvalidCnt, 'Added %s invalid rule%s: %s', $iterates[4]);
            $messages[] = $this->getStatusNotification($updInvalidCnt, 'Updated %s invalid rule%s: %s', $iterates[2]);
            $messages[] = $this->getStatusNotification($missingCnt, 'Missing %s rule%s: %s', array_column($newMissingRules, 'uid'));

            $msg = join(";\n", array_filter($messages));

            if ($msg) {
                $pageParams['ERROR_MESSAGE'] = $msg;
            }

            if (!array_key_exists('ERROR_MESSAGE', $pageParams) && !array_key_exists('SUCCESS_MESSAGE', $pageParams)) {
                $activeCnt      = count($iterates[1]);
                $invalidCnt     = count($iterates[0]);

                $msg = sprintf('Rules refreshed (%s rule%s active', $activeCnt, ($activeCnt > 1 ? 's' : ''));
                if ($invalidCnt) {
                    $msg .= sprintf(', %s invalid', $invalidCnt);
                }
                if ($oldMissingCnt) {
                    $msg .= sprintf(', %s missing', $oldMissingCnt);
                }

                $msg .= ')';
                $pageParams['SUCCESS_MESSAGE'] = $msg;
            }
        }

        return $pageParams;
    }

    public function updateRules(bool $localRules = true): array {
        // get all rules from db by uid; will not return classes with filename mismatch or invalid classname
        $currentRules   = tirreno('models')->rules->getAll();

        $sortedRules = [];
        foreach ($currentRules as $rule) {
            $sortedRules[$rule['uid']] = $rule;
        }

        $iterates       = [[], [], [], [], [], []];
        $metUids        = [];

        //$parentClass = \Tirreno\Controllers\Admin\Rules\Set\BaseRule::class;
        $parentClass = \Tirreno\Assets\Rule::class;
        $mtd         = 'defineCondition';

        $mainClasses    = tirreno('assets')->rules->getRulesClasses(true);
        // local classes first to keep ability to override default classes
        $allClassesFromFiles = $localRules ? tirreno('assets')->rules->getRulesClasses(false)['imported'] : [];
        $allClassesFromFiles += $mainClasses['imported'];

        foreach ($allClassesFromFiles as $uid => $cls) {
            $valid = true;

            $name   = constant("$cls::NAME") ?? '';
            $descr  = constant("$cls::DESCRIPTION") ?? '';
            $attr   = constant("$cls::ATTRIBUTES") ?? [];

            $obj = [
                'uid'           => $uid,
                'name'          => $name,
                'descr'         => $descr,
                'attributes'    => $attr,
            ];

            // check constants
            if (!is_string($name) || !is_string($descr) || !is_array($attr)) {
                $valid = false;
                $obj['name']        = '';
                $obj['descr']       = '';
                $obj['attributes']  = [];
            // check if rule is child class of Rule and defineCondition() was implemented
            } elseif (!is_subclass_of($cls, $parentClass) || (new \ReflectionMethod($cls, $mtd))->isAbstract()) {
                $valid = false;
            }

            $status = $this->addRule($sortedRules, $obj, $valid);
            $iterates[($status === null ? 0 : 1 + tirreno('utils')->conversion->intVal($status, 0)) * 2 + tirreno('utils')->conversion->intVal($valid, 0)][] = $uid;
            $metUids[] = $uid;
        }

        $flipMetUids = array_flip($metUids);
        $newMissingRules = [];
        $oldMissingCnt = 0;
        foreach ($sortedRules as $uid => $rule) {
            if (!array_key_exists($uid, $flipMetUids)) {
                if (!$rule['missing']) {
                    $newMissingRules[$uid] = $rule;
                    tirreno('models')->rules->setMissingByUid($uid);
                } else {
                    $oldMissingCnt += 1;
                }
            }
        }

        return [
            'iterates'              => $iterates,
            'oldMissingCnt'         => $oldMissingCnt,
            'newMissingRules'       => $newMissingRules,
        ];
    }

    private function getStatusNotification(int $cnt, string $template, array $data): ?string {
        if (!$cnt) {
            return null;
        }

        $str = join(', ', array_slice($data, 0, 10, true)) . ($cnt > 10 ? '&hellip;' : '.');

        return sprintf($template, strval($cnt), ($cnt > 1 ? 's' : ''), $str);
    }

    private function addRule(array $existingArray, array $obj, bool $valid): ?bool {
        $data = $existingArray[$obj['uid']] ?? null;
        $result = null;

        sort($obj['attributes']);

        if ($data === null) {
            $result = true;
        } else {
            $data['attributes'] = json_decode($data['attributes']);
            sort($data['attributes']);

            foreach ($obj as $key => $value) {
                if ($value !== $data[$key]) {
                    $result = false;
                    break;
                }
            }

            if ($result !== false && $data['validated'] !== $valid) {
                $result = false;
            }
        }

        if ($result !== null || $data['missing']) {
            tirreno('models')->rules->addRule($obj['uid'], $obj['name'], $obj['descr'], $obj['attributes'], $valid);
        }

        return ($data !== null && $data['missing']) ? true : $result;
    }

    public function changeThresholdValues(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'keyId', 'blacklist-threshold', 'review-queue-threshold']);
        $errorCode = tirreno('utils')->validators->validateThresholdValues($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId                  = tirreno('utils')->conversion->getIntRequestParam('keyId');
            $blacklistThreshold     = tirreno('utils')->conversion->getIntRequestParam('blacklist-threshold', true) ?? -1;
            $reviewQueueThreshold   = tirreno('utils')->conversion->getIntRequestParam('review-queue-threshold');

            $key = tirreno('models')->apiKeys->getKeyById($keyId);

            $recalculateReviewQueueCnt = $key['review_queue_threshold'] !== $reviewQueueThreshold;

            tirreno('models')->apiKeys->updateBlacklistThreshold($blacklistThreshold, $keyId);
            tirreno('models')->apiKeys->updateReviewQueueThreshold($reviewQueueThreshold, $keyId);

            if ($recalculateReviewQueueCnt) {
                tirreno('controllers')->reviewQueue->setNotReviewedCount(false, $keyId);
            }

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('rules_thresholdValues_update_success_message');
        }

        return $pageParams;
    }

    public function applyRulesPreset(): array {
        $pageParams = [];
        $params = tirreno('utils')->render->extractRequestParams(['token', 'keyId', 'rules-preset']);
        $errorCode = tirreno('utils')->validators->validateRulesPreset($params);

        if ($errorCode) {
            $pageParams['ERROR_CODE'] = $errorCode;
        } else {
            $keyId                  = tirreno('utils')->conversion->getIntRequestParam('keyId');
            $rulePresetName         = tirreno('utils')->conversion->getStringRequestParam('rules-preset');

            $this->applyRulesPresetById($rulePresetName, $keyId);

            $pageParams['SUCCESS_MESSAGE'] = tirreno('storage')->get('rules_applyRulesPresets_success_message');
        }

        return $pageParams;
    }

    public function applyRulesPresetById(string $presetId, int $apiKey): void {
        $rules = tirreno('assets')->rulesPresets->getPresets();
        if (!array_key_exists($presetId, $rules)) {
            return;
        }

        $defaultRules = $rules[$presetId]['main'];

        if (tirreno('utils')->variables->getEmailPhoneAllowed()) {
            $defaultRules = array_merge($defaultRules, $rules[$presetId]['additional']);
        }

        $currentRules = tirreno('models')->operatorsRules->getAllRulesByOperator($apiKey);
        if ($currentRules) {
            // remove old values!
            foreach (array_keys($currentRules) as $uid) {
                tirreno('models')->operatorsRules->updateRule($uid, 0, $apiKey);
            }
        }

        foreach ($defaultRules as $key => $value) {
            tirreno('models')->operatorsRules->updateRule($key, $value, $apiKey);
        }
    }

    public function saveUserRule(string $ruleUid, int $score, int $apiKey): void {
        tirreno('models')->operatorsRules->updateRule($ruleUid, $score, $apiKey);
    }

    public function saveRuleProportion(string $ruleUid, float $proportion, int $apiKey): void {
        tirreno('models')->operatorsRules->updateRuleProportion($ruleUid, $proportion, $apiKey);
    }

    public function getRuleProportion(int $totalUsers, int $ruleUsers): float {
        if ($ruleUsers === 0 || $totalUsers === 0) {
            return 0.0;
        }

        $proportion = (float) (100 * $ruleUsers) / (float) $totalUsers;

        // if number is too small make it a bit greater so it will be written in db as 0 < n < 1
        return abs($proportion) < 0.001 ? 0.001 : $proportion;
    }

    // return array of uids on each account of triggered rules
    private function evaluateRules(array $accountIds, array $rules, int $apiKey): array {
        $result = array_fill_keys($accountIds, []);

        $context = [];
        $record = [];

        foreach (array_chunk($accountIds, tirreno('utils')->variables->getRuleUsersBatchSize()) as $batch) {
            $context = $this->contextController->getContextByAccountIds($batch, $apiKey);
            foreach ($batch as $user) {
                $record = $context[$user] ?? null;
                if ($record) {
                    foreach ($rules as $rule) {
                        if ($this->executeRule($rule, $record)) {
                            $result[$user][] = $rule->uid;
                        }
                    }
                }
            }
        }

        return $result;
    }

    private function executeRule(\Tirreno\Assets\Rule $rule, array $params): bool {
        $executed = false;

        try {
            $rule->updateParams($params);
            $executed = $rule->execute();
        } catch (\Throwable $e) {
            if (defined($rule->uid)) {
                tirreno('models')->rules->setInvalidByUid($rule->uid);
            }

            tirreno('log')->error('failed to execute rule %s: %s.', $rule->uid, $e->getMessage());
        }

        return $executed;
    }

    public function checkRule(string $ruleUid, int $apiKey): array {
        $users = tirreno('models')->users->getLastNUsers(tirreno('utils')->variables->getCheckRuleUsersLimit(), $apiKey);
        $accounts = [];
        foreach ($users as $user) {
            $accounts[$user['accountid']] = $user;
        }
        $accountIds = array_keys($accounts);

        $this->buildEvaluationModels($ruleUid);

        $targetRule = $this->rulesModel->getRuleWithOperatorValue($ruleUid, $apiKey);

        if ($targetRule === [] || !array_key_exists($ruleUid, $this->rulesMap)) {
            return [0, []];
        }

        $results = $this->evaluateRules($accountIds, [$this->rulesMap[$ruleUid]], $apiKey);
        $matchingAccountIds = array_keys(array_filter($results, static function ($value): bool {
            return $value !== [];
        }));

        $result = [];
        foreach ($matchingAccountIds as $id) {
            if (array_key_exists($id, $accounts)) {
                $result[$id] = $accounts[$id];
            }
        }

        return [count($accountIds), $result];
    }

    public function evaluateUser(int $accountId, int $apiKey, bool $preparedModels = false): void {
        if (!$preparedModels || !isset($this->rulesModel)) {
            $this->buildEvaluationModels();
        }

        foreach ($this->totalModels as $model) {
            $model->updateTotalsByAccountIds([$accountId], $apiKey);
        }

        // grab all rules
        $operatorRules = $this->getAllRulesWithOperatorValues($apiKey);
        $primarySetId = tirreno('utils')->constants->PRIMARY_RULES_SET_ID;
        $firstSet = $operatorRules[$primarySetId];

        // execute the first set
        $rules = array_intersect_key($this->rulesMap, $firstSet);
        $result = $this->evaluateRules([$accountId], $rules, $apiKey);
        $uids = $result[$accountId];

        // we have list of uids and a list of sets rules

        $details = [];
        // for each set
        foreach ($operatorRules as $setId => $set) {
            $details[$setId] = [];

            foreach ($uids as $uid) {
                $details[$setId][] = [
                    'uid'       => $uid,
                    'score'     => $set[$uid]['value'] ?? 0,
                ];
            }
        }

        $setIds = array_keys($details);
        $scores = [];
        foreach ($setIds as $setId) {
            $scores[$setId] = $this->normalizeScore($details[$setId]);
        }

        $firstScore = $scores[$primarySetId];
        $firstDetails = $details[$primarySetId];

        $cron = $preparedModels;
        $this->userController->updateUserStatus($firstScore, json_encode($firstDetails), $cron, $accountId, $apiKey);

        // only event_account_score update
        $this->userController->updateUserScore($scores, $details, $accountId, $apiKey);
    }

    public function buildEvaluationModels(?string $uid = null): void {
        $this->totalModels = [];
        foreach (tirreno('utils')->constants->RULES_TOTALS_MODELS as $className) {
            $this->totalModels[] = new $className();
        }

        $this->contextController    = tirreno('controllers')->context;
        $this->userController       = tirreno('controllers')->user;
        $this->rulesModel           = tirreno('models')->operatorsRules;

        $ruleBuilder = new \Ruler\RuleBuilder();

        if ($uid) {
            $ruleObj = tirreno('assets')->rules->getSingleRuleObject($uid, $ruleBuilder);
            $this->rulesMap = $ruleObj ? [$uid => $ruleObj] : [];
        } else {
            $this->rulesMap = tirreno('assets')->rules->getAllRulesObjects($ruleBuilder);
        }
    }

    private function normalizeScore(array $data): int {
        $scores = array_column($data, 'score');
        $totalScore = max(array_sum($scores), 0);

        $filterScores = array_filter($scores, function ($value) {
            return $value > 0;
        });

        $matches = count($filterScores);

        return max(tirreno('utils')->conversion->intVal((99 - ($totalScore * (pow($matches, 1.1) - $matches + 1))), 0), 0);
    }

    // do not filter by attributes if data is needed only for rendering info
    public function getAllRulesByApiKey(int $apiKey): array {
        $rules = tirreno('models')->operatorsRules->getAllRulesByOperatorAndSet(tirreno('utils')->constants->PRIMARY_RULES_SET_ID, $apiKey);

        $results = [];
        foreach ($rules as $rule) {
            $rule['type'] = tirreno('assets')->rules->getRuleTypeByUid($rule['uid']);
            $results[] = $rule;
        }

        usort($results, [\Tirreno\Utils\Sort::class, 'cmpRule']);

        return $results;
    }

    // only suitable for execution -- valid, not missing, with fitting attributes, returning associative array
    private function getAllRulesWithOperatorValues(int $apiKey): array {
        $skipAttributes = tirreno('models')->apiKeys->getSkipEnrichingAttributes($apiKey);
        $rules = tirreno('models')->operatorsRules->getAllValidRulesByOperator($apiKey);

        return $this->filterRulesByAttributesAddTypes($rules, $skipAttributes);
    }

    // set 1 has all of the suitable rules even with 0 values
    private function filterRulesByAttributesAddTypes(array $rules, array $skipAttributes): array {
        $results = [];

        $setId = tirreno('utils')->constants->PRIMARY_RULES_SET_ID;
        $ruleSet = $rules[$setId] ?? [];

        $results[$setId] = [];

        foreach ($ruleSet as $uid => $row) {
            if (!count(array_intersect(json_decode($row['attributes']), $skipAttributes))) {
                $row['type'] = tirreno('assets')->rules->getRuleTypeByUid($row['uid']);
                $results[$setId][$uid] = $row;
            }
        }

        return $results;
    }

    public function getList(int $apiKey): array {
        return tirreno('grids')->rules->getAll($apiKey);
    }
}
