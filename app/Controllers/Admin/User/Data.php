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

namespace Tirreno\Controllers\Admin\User;

class Data extends \Tirreno\Controllers\Admin\Base\Data {
    public function proceedPostRequest(): array {
        return match (\Tirreno\Utils\Conversion::getStringRequestParam('cmd')) {
            'riskScore'     => $this->recalculateRiskScore(),
            'reenrichment'  => $this->enrichEntity(),
            'delete'        => $this->deleteUser(),
            default => []
        };
    }

    public function recalculateRiskScore(): array {
        $result = [];
        set_error_handler([\Tirreno\Utils\ErrorHandler::class, 'exceptionErrorHandler']);

        try {
            $apiKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorApiKeyId();
            $userId = \Tirreno\Utils\Conversion::getIntRequestParam('accountid');

            [$score, $rules] = $this->getUserScore($userId, $apiKey);
            $result = [
                'SUCCESS_MESSAGE' => $this->f3->get('AdminUser_recalculate_risk_score_success_message'),
                'score' => $score,
                'rules' => $rules,
            ];
        } catch (\ErrorException $e) {
            $result = ['ERROR_CODE' => \Tirreno\Utils\ErrorCodes::RISK_SCORE_UPDATE_UNKNOWN_ERROR];
        }

        restore_error_handler();

        return $result;
    }

    public function enrichEntity(): array {
        $dataController = new \Tirreno\Controllers\Admin\Enrichment\Data();
        $apiKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorApiKeyId();
        $enrichmentKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString();

        $type       = \Tirreno\Utils\Conversion::getStringRequestParam('type');
        $search     = \Tirreno\Utils\Conversion::getStringRequestParam('search', true);
        $entityId   = \Tirreno\Utils\Conversion::getIntRequestParam('entityId', true);

        return $dataController->enrichEntity($type, $search, $entityId, $apiKey, $enrichmentKey);
    }

    public function deleteUser(): void {
        $apiKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorApiKeyId();

        if ($apiKey) {
            $model = new \Tirreno\Models\Queue();
            $accountId = \Tirreno\Utils\Conversion::getIntRequestParam('accountid');
            $code = \Tirreno\Utils\ErrorCodes::REST_API_USER_ALREADY_DELETING;

            if (!$model->isInQueue($accountId, \Tirreno\Utils\Constants::get()->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey)) {
                $code = \Tirreno\Utils\ErrorCodes::REST_API_USER_ADDED_FOR_DELETION;
                $model->add($accountId, \Tirreno\Utils\Constants::get()->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey);
            }

            $this->f3->set('SESSION.extra_message_code', $code);
            $this->f3->reroute('/id');
        }
    }

    public function getUserScoreDetails(int $userId, int $apiKey): array {
        $model = new \Tirreno\Models\User();
        $user = $model->getUserById($userId, $apiKey);

        return [
            'score_details'     => $model->getApplicableRulesByAccountId($userId, $apiKey, true),
            'score_calculated'  => $user !== [] ? $user['score'] !== null : false,
        ];
    }

    public function getUserById(int $accountId, int $apiKey): array {
        $user = (new \Tirreno\Models\User())->getUserById($accountId, $apiKey);
        $rules = (new \Tirreno\Models\Rules())->getAll();

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

        usort($details, [\Tirreno\Utils\Sort::class, 'cmpScore']);

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
        \Tirreno\Utils\Timezones::localizeTimestampsForActiveOperator($tsColumns, $user);

        return $user;
    }

    public function checkIfOperatorHasAccess(int $userId, int $apiKey): bool {
        return (new \Tirreno\Models\User())->checkAccess($userId, $apiKey);
    }

    public function checkEnrichmentAvailability(): bool {
        return \Tirreno\Utils\ApiKeys::getCurrentOperatorEnrichmentKeyString() !== null;
    }

    public function addToWatchlist(int $accountId, int $apiKey): void {
        $model = new \Tirreno\Models\Watchlist();
        $model->add($accountId, $apiKey);
    }

    public function removeFromWatchlist(int $accountId, int $apiKey): void {
        $model = new \Tirreno\Models\Watchlist();
        $model->remove($accountId, $apiKey);
    }

    public function addToBlacklistQueue(int $accountId, bool $fraud, bool $cron, bool $cnt, int $apiKey): void {
        $model = new \Tirreno\Models\Queue();
        $inQueue = $model->isInQueue($accountId, \Tirreno\Utils\Constants::get()->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey);

        if (!$fraud) {
            $this->setFraudFlag($accountId, false, $apiKey); // Directly remove blacklisted items

            if ($inQueue) {
                $model->removeFromQueue($accountId, \Tirreno\Utils\Constants::get()->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey); // Cancel queued operation
            }
        }

        if (!$inQueue && $fraud) {
            $model->add($accountId, \Tirreno\Utils\Constants::get()->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey);
        }

        $model = new \Tirreno\Models\User();
        $model->updateFraudFlag([$accountId], $apiKey, $fraud);

        if ($cnt) {
            $controller = new \Tirreno\Controllers\Admin\Blacklist\Data();
            $controller->setBlacklistUsersCount(false, $apiKey);        // do not use cache
            $controller = new \Tirreno\Controllers\Admin\ReviewQueue\Data();
            $controller->setNotReviewedCount(false, $apiKey);           // do not use cache
        }

        \Tirreno\Utils\Routes::callExtra('UPDATE_USER_FRAUD_STATUS', $accountId, $fraud, $cron, $apiKey);
    }

    public function addToCalulcateRiskScoreQueue(int $accountId): void {
        $apiKey = \Tirreno\Utils\ApiKeys::getCurrentOperatorApiKeyId();

        $model = new \Tirreno\Models\Queue();
        $inQueue = $model->isInQueue($accountId, \Tirreno\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE, $apiKey);

        if (!$inQueue) {
            $model->add($accountId, \Tirreno\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE, $apiKey);
        }
    }

    /**
     * @param array{accountId: int, key: int}[] $accounts
     */
    public function addBatchToCalulcateRiskScoreQueue(array $accounts): void {
        (new \Tirreno\Models\Queue())->addBatch($accounts, \Tirreno\Utils\Constants::get()->RISK_SCORE_QUEUE_ACTION_TYPE);
    }

    public function setReviewedFlag(int $accountId, bool $reviewed, int $apiKey): void {
        $model = new \Tirreno\Models\User();
        $model->updateReviewedFlag($accountId, $apiKey, $reviewed);
    }

    public function getUserScore(int $accountId, int $apiKey): array {
        $total = 0;
        $rules = [];

        $rulesController = new \Tirreno\Controllers\Admin\Rules\Data();
        $rulesController->evaluateUser($accountId, $apiKey);

        $model = new \Tirreno\Models\User();
        $rules = $model->getApplicableRulesByAccountId($accountId, $apiKey);

        $total = $rules[0]['total_score'] ?? 0;
        array_walk($rules, function (&$rule): void {
            unset($rule['total_score']);
        }, $rules);

        return [$total, $rules];
    }

    public function getScheduledForDeletion(int $userId, int $apiKey): array {
        $model = new \Tirreno\Models\Queue();

        [$scheduled, $status] = $model->isInQueueStatus($userId, \Tirreno\Utils\Constants::get()->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey);

        return [$scheduled, ($status === \Tirreno\Utils\Constants::get()->FAILED_QUEUE_STATUS_TYPE) ? \Tirreno\Utils\ErrorCodes::USER_DELETION_FAILED : null];
    }

    public function getScheduledForBlacklist(int $userId, int $apiKey): array {
        $model = new \Tirreno\Models\Queue();

        [$scheduled, $status] = $model->isInQueueStatus($userId, \Tirreno\Utils\Constants::get()->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey);

        return [$scheduled, ($status === \Tirreno\Utils\Constants::get()->FAILED_QUEUE_STATUS_TYPE) ? \Tirreno\Utils\ErrorCodes::USER_BLACKLISTING_FAILED : null];
    }

    public function setFraudFlag(int $accountId, bool $fraud, int $apiKey): array {
        $blacklistItemsModel = new \Tirreno\Models\BlacklistItems();

        $ips = $blacklistItemsModel->getIpsRelatedToAccountWithinOperator($accountId, $apiKey);
        $emails = $blacklistItemsModel->getEmailsRelatedToAccountWithinOperator($accountId, $apiKey);
        $phones = $blacklistItemsModel->getPhonesRelatedToAccountWithinOperator($accountId, $apiKey);

        $relatedIpsIds = array_column($ips, 'id');
        $relatedEmailsIds = array_column($emails, 'id');
        $relatedPhonesIds = array_column($phones, 'id');

        $ips = $blacklistItemsModel->getIpsRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedIpsIds = array_column($ips, 'id');
        if (count($relatedIpsIds) !== 0) {
            $model = new \Tirreno\Models\Ip();
            $model->updateFraudFlag($relatedIpsIds, $fraud, $apiKey);
        }

        $emails = $blacklistItemsModel->getEmailsRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedEmailsIds = array_column($emails, 'id');
        if (count($relatedEmailsIds) !== 0) {
            $model = new \Tirreno\Models\Email();
            $model->updateFraudFlag($relatedEmailsIds, $fraud, $apiKey);
        }

        $phones = $blacklistItemsModel->getPhonesRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedPhonesIds = array_column($phones, 'id');
        if (count($relatedPhonesIds) !== 0) {
            $model = new \Tirreno\Models\Phone();
            $model->updateFraudFlag($relatedPhonesIds, $fraud, $apiKey);
        }

        return array_merge($ips, $emails, $phones);
    }

    public function updateUserStatus(int $score, string $details, bool $cron, int $accountId, int $apiKey): void {
        $addToReview = false;

        $model = new \Tirreno\Models\ApiKeys();
        $key = $model->getKeyById($apiKey);

        $userModel = new \Tirreno\Models\User();

        if ($score <= $key['blacklist_threshold']) {
            $this->addToBlacklistQueue($accountId, true, true, false, $apiKey); // automatic blacklist anyway, do not recalculate
        } elseif ($score <= $key['review_queue_threshold']) {
            $user = $userModel->getUserById($accountId, $apiKey);
            $addToReview = $user['added_to_review'] === null && $user['fraud'] === null;

            if (!$cron && $addToReview) {
                $controller = new \Tirreno\Controllers\Admin\ReviewQueue\Data();
                $controller->setNotReviewedCount(false, $apiKey);           // do not use cache
            }
        }

        \Tirreno\Utils\Routes::callExtra('UPDATE_USER_STATUS', $score, $details, $addToReview, $cron, $accountId, $apiKey);

        $userModel->updateUserStatus($score, $details, $addToReview, $accountId, $apiKey);
    }
}
