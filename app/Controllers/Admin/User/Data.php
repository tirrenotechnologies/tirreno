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

namespace Controllers\Admin\User;

class Data extends \Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return match (\Utils\Conversion::getStringRequestParam('cmd')) {
            'riskScore'     => $this->recalculateRiskScore(),
            'reenrichment'  => $this->enrichEntity(),
            'delete'        => $this->deleteUser(),
            default => []
        };
    }

    public function recalculateRiskScore(): array {
        $result = [];
        set_error_handler([\Utils\ErrorHandler::class, 'exceptionErrorHandler']);

        try {
            $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
            $userId = \Utils\Conversion::getIntRequestParam('accountid');

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

    public function enrichEntity(): array {
        $dataController = new \Controllers\Admin\Enrichment\Data();
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();

        $type       = \Utils\Conversion::getStringRequestParam('type');
        $search     = \Utils\Conversion::getStringRequestParam('search', true);
        $entityId   = \Utils\Conversion::getIntRequestParam('entityId', true);

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function deleteUser(): void {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();

        if ($apiKey) {
            $model = new \Models\Queue();
            $accountId = \Utils\Conversion::getIntRequestParam('accountid');
            $code = \Utils\ErrorCodes::REST_API_USER_ALREADY_DELETING;

            if (!$model->isInQueue($accountId, \Utils\Constants::get('DELETE_USER_QUEUE_ACTION_TYPE'), $apiKey)) {
                $code = \Utils\ErrorCodes::REST_API_USER_ADDED_FOR_DELETION;
                $model->add($accountId, \Utils\Constants::get('DELETE_USER_QUEUE_ACTION_TYPE'), $apiKey);
            }

            $this->f3->set('SESSION.extra_message_code', $code);
            $this->f3->reroute('/id');
        }
    }

    public function getUserScoreDetails(int $userId, int $apiKey): array {
        $model = new \Models\User();
        $user = $model->getUser($userId, $apiKey);

        return [
            'score_details'     => $model->getApplicableRulesByAccountId($userId, $apiKey, true),
            'score_calculated'  => $user !== [] ? $user['score'] !== null : false,
        ];
    }

    public function getUserById(int $accountId, int $apiKey): array {
        $user = (new \Models\User())->getUser($accountId, $apiKey);
        $rules = (new \Models\Rules())->getAll();

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

        usort($details, [\Utils\Sort::class, 'cmpScore']);

        $user['score_details'] = $details;

        $pageTitle = $user['userid'];
        if ($user['firstname'] !== null && $user['firstname'] !== '') {
            $pageTitle .= sprintf(' (%s)', $user['firstname']);
        }
        if ($user['lastname'] !== null && $user['lastname'] !== '') {
            $pageTitle .= sprintf(' (%s)', $user['lastname']);
        }
        $user['page_title'] = $pageTitle;

        $tsColumns = ['created', 'lastseen', 'score_updated_at', 'latest_decision', 'updated', 'added_to_review'];
        \Utils\TimeZones::localizeTimestampsForActiveOperator($tsColumns, $user);

        return $user;
    }

    public function checkIfOperatorHasAccess(int $userId, int $apiKey): bool {
        return (new \Models\User())->checkAccess($userId, $apiKey);
    }

    public function checkEnrichmentAvailability(): bool {
        return \Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString() !== null;
    }

    public function addToWatchlist(int $accountId, int $apiKey): void {
        $model = new \Models\Watchlist();
        $model->add($accountId, $apiKey);
    }

    public function removeFromWatchlist(int $accountId, int $apiKey): void {
        $model = new \Models\Watchlist();
        $model->remove($accountId, $apiKey);
    }

    public function addToBlacklistQueue(int $accountId, bool $fraud, bool $cron, bool $cnt, int $apiKey): void {
        $model = new \Models\Queue();
        $inQueue = $model->isInQueue($accountId, \Utils\Constants::get('BLACKLIST_QUEUE_ACTION_TYPE'), $apiKey);

        if (!$fraud) {
            $this->setFraudFlag($accountId, false, $apiKey); // Directly remove blacklisted items

            if ($inQueue) {
                $model->removeFromQueue($accountId, \Utils\Constants::get('BLACKLIST_QUEUE_ACTION_TYPE'), $apiKey); // Cancel queued operation
            }
        }

        if (!$inQueue && $fraud) {
            $model->add($accountId, \Utils\Constants::get('BLACKLIST_QUEUE_ACTION_TYPE'), $apiKey);
        }

        $model = new \Models\User();
        $model->updateFraudFlag([$accountId], $apiKey, $fraud);

        if ($cnt) {
            $controller = new \Controllers\Admin\Blacklist\Data();
            $controller->setBlacklistUsersCount(false, $apiKey);        // do not use cache
            $controller = new \Controllers\Admin\ReviewQueue\Data();
            $controller->setNotReviewedCount(false, $apiKey);           // do not use cache
        }

        \Utils\Routes::callExtra('UPDATE_USER_FRAUD_STATUS', $accountId, $fraud, $cron, $apiKey);
    }

    public function addToCalulcateRiskScoreQueue(int $accountId): void {
        $apiKey = \Utils\ApiKeys::getCurrentOperatorApiKeyId();

        $model = new \Models\Queue();
        $inQueue = $model->isInQueue($accountId, \Utils\Constants::get('RISK_SCORE_QUEUE_ACTION_TYPE'), $apiKey);

        if (!$inQueue) {
            $model->add($accountId, \Utils\Constants::get('RISK_SCORE_QUEUE_ACTION_TYPE'), $apiKey);
        }
    }

    /**
     * @param array{accountId: int, key: int}[] $accounts
     */
    public function addBatchToCalulcateRiskScoreQueue(array $accounts): void {
        (new \Models\Queue())->addBatch($accounts, \Utils\Constants::get('RISK_SCORE_QUEUE_ACTION_TYPE'));
    }

    public function setReviewedFlag(int $accountId, bool $reviewed, int $apiKey): void {
        $model = new \Models\User();
        $model->updateReviewedFlag($accountId, $apiKey, $reviewed);
    }

    public function getUserScore(int $accountId, int $apiKey): array {
        $total = 0;
        $rules = [];

        $rulesController = new \Controllers\Admin\Rules\Data();
        $rulesController->evaluateUser($accountId, $apiKey);

        $model = new \Models\User();
        $rules = $model->getApplicableRulesByAccountId($accountId, $apiKey);

        $total = $rules[0]['total_score'] ?? 0;
        array_walk($rules, function (&$rule): void {
            unset($rule['total_score']);
        }, $rules);

        return [$total, $rules];
    }

    public function getScheduledForDeletion(int $userId, int $apiKey): array {
        $model = new \Models\Queue();

        [$scheduled, $status] = $model->isInQueueStatus($userId, \Utils\Constants::get('DELETE_USER_QUEUE_ACTION_TYPE'), $apiKey);

        return [$scheduled, ($status === \Utils\Constants::get('FAILED_QUEUE_STATUS_TYPE')) ? \Utils\ErrorCodes::USER_DELETION_FAILED : null];
    }

    public function getScheduledForBlacklist(int $userId, int $apiKey): array {
        $model = new \Models\Queue();

        [$scheduled, $status] = $model->isInQueueStatus($userId, \Utils\Constants::get('BLACKLIST_QUEUE_ACTION_TYPE'), $apiKey);

        return [$scheduled, ($status === \Utils\Constants::get('FAILED_QUEUE_STATUS_TYPE')) ? \Utils\ErrorCodes::USER_BLACKLISTING_FAILED : null];
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

    public function updateUserStatus(int $accountId, array $scoreData, bool $cron, int $apiKey): void {
        $scoreData['addToReview'] = false;

        $keyModel = new \Models\ApiKeys();
        $keyModel->getKeyById($apiKey);

        $userModel = new \Models\User();

        if ($scoreData['score'] <= $keyModel->blacklist_threshold) {
            $this->addToBlacklistQueue($accountId, true, true, false, $apiKey); // automatic blacklist anyway, do not recalculate
        } elseif ($scoreData['score'] <= $keyModel->review_queue_threshold) {
            $data = $userModel->getUser($accountId, $apiKey);
            $scoreData['addToReview'] = $data['added_to_review'] === null && $data['fraud'] === null;
            if (!$cron && $scoreData['addToReview']) {
                $controller = new \Controllers\Admin\ReviewQueue\Data();
                $controller->setNotReviewedCount(false, $apiKey);           // do not use cache
            }
        }

        \Utils\Routes::callExtra('UPDATE_USER_STATUS', $accountId, $scoreData, $cron, $apiKey);

        $userModel->updateUserStatus($accountId, $scoreData, $apiKey);
    }
}
