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

namespace Controllers\Admin\User;

class Data extends \Controllers\Base {
    use \Traits\ApiKeys;

    public function proceedPostRequest(array $params): array {
        return match ($params['cmd']) {
            'riskScore' => $this->recalculateRiskScore($params),
            'reenrichment' => $this->enrichEntity($params),
            'delete' => $this->deleteUser($params),
            default => []
        };
    }

    public function recalculateRiskScore(array $params): array {
        $result = [];
        set_error_handler([\Utils\ErrorHandler::class, 'exceptionErrorHandler']);

        try {
            $apiKey = $this->getCurrentOperatorApiKeyId();
            $userId = (int) $params['accountid'];

            [$score, $rules] = $this->getUserScore($userId, $apiKey);
            $result = [
                'SUCCESS_MESSAGE' => $this->f3->get('AdminUser_recalculate_risk_score_success_message'),
                'score' => $score,
                'rules' => $rules,
            ];
        } catch (\ErrorException $e) {
            $result = ['ERROR_CODE' => \Utils\ErrorCodes::RISK_SCORE_UPDATE_UNKNOWN_ERROR];
        }

        restore_error_handler();

        return $result;
    }

    public function enrichEntity(array $params): array {
        $dataController = new \Controllers\Admin\Enrichment\Data();
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $enrichmentKey = $this->getCurrentOperatorEnrichmentKeyString();
        $type = $params['type'];
        $search = $params['search'] ?? null;
        $entityId = isset($params['entityId']) ? (int) $params['entityId'] : null;

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function deleteUser(array $params): void {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        if ($apiKey) {
            $accountId = (int) $params['accountid'];
            $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Delete);
            $accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

            $code = \Utils\ErrorCodes::REST_API_USER_ALREADY_SCHEDULED_FOR_DELETION;
            if (!$accountOpQueueModel->isInQueue($accountId, $apiKey)) {
                $code = \Utils\ErrorCodes::REST_API_USER_SUCCESSFULLY_ADDED_FOR_DELETION;
                $accountOpQueueModel->add($accountId, $apiKey);
            }

            $this->f3->set('SESSION.extra_message_code', $code);
            $this->f3->reroute('/id');
        }
    }

    public function getUserScoreDetails(int $userId, int $apiKey): array {
        $model = new \Models\User();
        $user = $model->getUser($userId, $apiKey);

        return [
            'score_details'     => $model->getApplicableRulesByAccountId($userId, $apiKey),
            'score_calculated'  => $user !== [] ? $user['score'] !== null : false,
        ];
    }

    public function getUserById(int $accountId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();

        $model = new \Models\User();
        $user = $model->getUser($accountId, $apiKey);

        $model = new \Models\Rules();
        $rules = $model->getAll();

        $details = [];
        if ($user['score_details']) {
            $scoreDetails = json_decode($user['score_details'], true);

            foreach ($scoreDetails as $detail) {
                $score = $detail['score'] ?? null;
                $ruleUid = $detail['uid'] ?? null;
                if ($score !== 0 && isset($rules[$ruleUid])) {
                    $item = $rules[$ruleUid];
                    $item['score'] = $score;
                    $details[] = $item;
                }
            }
        }

        usort($details, static function ($a, $b): int {
            return $b['score'] <=> $a['score'];
        });

        $user['score_details'] = $details;

        $pageTitle = $user['userid'];
        if ($user['firstname'] !== null && $user['firstname'] !== '') {
            $pageTitle .= sprintf(' (%s)', $user['firstname']);
        }
        if ($user['lastname'] !== null && $user['lastname'] !== '') {
            $pageTitle .= sprintf(' (%s)', $user['lastname']);
        }
        $user['page_title'] = $pageTitle;

        return $user;
    }

    public function checkIfOperatorHasAccess(int $userId): bool {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $userModel = new \Models\User();

        return $userModel->checkAccess($userId, $apiKey);
    }

    public function addToWatchlist(int $accountId, int $apiKey): void {
        $model = new \Models\Watchlist();
        $model->add($accountId, $apiKey);
    }

    public function removeFromWatchlist(int $accountId, int $apiKey): void {
        $model = new \Models\Watchlist();
        $model->remove($accountId, $apiKey);
    }

    public function addToBlacklistQueue(int $accountId, bool $fraud, int $apiKey): void {
        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Blacklist);
        $accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);
        $inQueue = $accountOpQueueModel->isInQueue($accountId, $apiKey);

        if (!$fraud) {
            $this->setFraudFlag($accountId, false, $apiKey); // Directly remove blacklisted items

            if ($inQueue) {
                $accountOpQueueModel->removeFromQueue(); // Cancel queued operation
            }
        }

        if (!$inQueue && $fraud) {
            $accountOpQueueModel->add($accountId, $apiKey);
        }

        $model = new \Models\User();
        $model->updateFraudFlag([$accountId], $apiKey, $fraud);
    }

    public function addToCalulcateRiskScoreQueue(int $accountId): void {
        $apiKey = $this->getCurrentOperatorApiKeyObject();
        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::CalulcateRiskScore);
        $accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);
        $inQueue = $accountOpQueueModel->isInQueue($accountId, $apiKey->id);

        if (!$inQueue) {
            $accountOpQueueModel->add($accountId, $apiKey->id);
        }
    }

    /**
     * @param array{accountId: int, key: int}[] $accounts
     */
    public function addBatchToCalulcateRiskScoreQueue(array $accounts): void {
        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::CalulcateRiskScore);
        $accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

        $accountOpQueueModel->addBatch($accounts);
    }

    public function setReviewedFlag(int $accountId, bool $reviewed, int $apiKey): void {
        $model = new \Models\User();
        $model->updateReviewedFlag($accountId, $apiKey, $reviewed);
    }

    public function getUserScore(int $accountId, int $apiKey): array {
        $total = 0;
        $rules = [];

        $rulesController = new \Controllers\Admin\Rules\Data();
        $rulesController->updateScoreByAccountId($accountId, $apiKey);

        $model = new \Models\User();
        $rules = $model->getApplicableRulesByAccountId($accountId, $apiKey);

        $total = $rules[0]['total_score'] ?? 0;
        array_walk($rules, function (&$rule): void {
            unset($rule['total_score']);
        }, $rules);

        return [$total, $rules];
    }

    public function validate(int $accountId, array $params): int|false {
        $errorCode = \Utils\Access::CSRFTokenValid($params, $this->f3);
        if ($errorCode) {
            return $errorCode;
        }

        $userHasAccess = $this->checkIfOperatorHasAccess($accountId);

        return !$userHasAccess ? \Utils\ErrorCodes::OPERATOR_DOES_NOT_HAVE_ACCESS_TO_ACCOUNT : false;
    }

    public function getScheduledForDeletion(int $userId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Delete);
        $accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

        [$scheduled, $status] = $accountOpQueueModel->isInQueueStatus($userId, $apiKey);

        return [$scheduled, ($status === \Type\QueueAccountOperationStatusType::Failed) ? \Utils\ErrorCodes::USER_DELETION_FAILED : null];
    }

    public function getScheduledForBlacklist(int $userId): array {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $actionType = new \Type\QueueAccountOperationActionType(\Type\QueueAccountOperationActionType::Blacklist);
        $accountOpQueueModel = new \Models\Queue\AccountOperationQueue($actionType);

        [$scheduled, $status] = $accountOpQueueModel->isInQueueStatus($userId, $apiKey);

        return [$scheduled, ($status === \Type\QueueAccountOperationStatusType::Failed) ? \Utils\ErrorCodes::USER_BLACKLISTING_FAILED : null];
    }

    public function getPayloadColumns($userId) {
        $apiKey = $this->getCurrentOperatorApiKeyId();
        $model = new \Models\Grid\Payloads\Grid($apiKey);

        return $model->getPayloadConfiguration($userId);
    }

    public function setFraudFlag(int $accountId, bool $fraud, int $apiKey): array {
        $blacklistItemsModel = new \Models\BlacklistItems();

        $ips = $blacklistItemsModel->getIpsRelatedToAccountWithinOperator($accountId, $apiKey);
        $emails = $blacklistItemsModel->getEmailsRelatedToAccountWithinOperator($accountId, $apiKey);
        $phones = $blacklistItemsModel->getPhonesRelatedToAccountWithinOperator($accountId, $apiKey);

        $relatedIpsIds = array_column($ips, 'id');
        $relatedEmailsIds = array_column($emails, 'id');
        $relatedPhonesIds = array_column($phones, 'id');

        $ips = $blacklistItemsModel->getIpsRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedIpsIds = array_column($ips, 'id');
        if (count($relatedIpsIds) !== 0) {
            $model = new \Models\Ip();
            $model->updateFraudFlag($relatedIpsIds, $fraud, $apiKey);
        }

        $emails = $blacklistItemsModel->getEmailsRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedEmailsIds = array_column($emails, 'id');
        if (count($relatedEmailsIds) !== 0) {
            $model = new \Models\Email();
            $model->updateFraudFlag($relatedEmailsIds, $fraud, $apiKey);
        }

        $phones = $blacklistItemsModel->getPhonesRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedPhonesIds = array_column($phones, 'id');
        if (count($relatedPhonesIds) !== 0) {
            $model = new \Models\Phone();
            $model->updateFraudFlag($relatedPhonesIds, $fraud, $apiKey);
        }

        return array_merge($ips, $emails, $phones);
    }
}
