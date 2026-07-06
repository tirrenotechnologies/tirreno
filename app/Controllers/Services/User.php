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

class User extends \Tirreno\Controllers\Services\Base {
    public function getSparklinesChart(int $apiKey): array {
        return tirreno('charts')->userStats->getData($apiKey);
    }

    public function getUserDetails(int $userId, int $apiKey): array {
        tirreno('models')->user->updateTotalsByAccountIds([$userId], $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Id();
        $userDetails    = $model->getDetails($userId, $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Ip();
        $ipDetails      = $model->getDetails($userId, $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Total();
        $totalDetails   = $model->getDetails($userId, $apiKey);

        $model          = new \Tirreno\Models\UserDetails\Behaviour();
        $offset         = tirreno('utils')->timezones->getCurrentOperatorOffset();

        $dateRange      = tirreno('utils')->timezones->getTodayRange($offset);
        $todayDetails   = $model->getDayDetails($userId, $dateRange, $apiKey);

        $dateRange          = tirreno('utils')->timezones->getYesterdayRange($offset);
        $yesterdayDetails   = $model->getDayDetails($userId, $dateRange, $apiKey);

        return [
            'userDetails'       => $userDetails,
            'ipDetails'         => $ipDetails,
            'totalDetails'      => $totalDetails,
            'todayDetails'      => $todayDetails,
            'yesterdayDetails'  => $yesterdayDetails,
        ];
    }

    public function recalculateRiskScore(int $apiKey): array {
        $result = [];
        set_error_handler([\Tirreno\Utils\ErrorHandler::class, 'exceptionErrorHandler']);

        try {
            $userId = tirreno('utils')->conversion->getIntRequestParam('accountid');

            [$score, $rules] = $this->getUserScore($userId, $apiKey);
            $result = [
                'SUCCESS_MESSAGE' => tirreno('storage')->get('user_recalculate_risk_score_success_message'),
                'score' => $score,
                'rules' => $rules,
            ];
        } catch (\ErrorException $e) {
            $result = ['ERROR_CODE' => tirreno('utils')->errorCodes->RISK_SCORE_UPDATE_UNKNOWN_ERROR];
        }

        restore_error_handler();

        return $result;
    }

    public function deleteUser(int $apiKey): void {
        // TODO: check apiKey + account owning
        if ($apiKey) {
            $accountId = tirreno('utils')->conversion->getIntRequestParam('accountid');
            $code = tirreno('utils')->errorCodes->REST_API_USER_ALREADY_DELETING;

            if (!tirreno('models')->queue->isInQueue($accountId, tirreno('utils')->constants->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey)) {
                $code = tirreno('utils')->errorCodes->REST_API_USER_ADDED_FOR_DELETION;
                tirreno('models')->queue->add($accountId, tirreno('utils')->constants->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey);
            }

            tirreno('session')->set('extra_message_code', $code);
            tirreno('response')->redirect('/id');
        }
    }

    public function getUserScoreDetails(int $userId, int $apiKey): array {
        $user = tirreno('models')->user->getUserById($userId, $apiKey);

        return [
            'score_details'     => tirreno('models')->user->getApplicableRulesByAccountId($userId, $apiKey, true),
            'score_calculated'  => $user !== [] ? $user['score'] !== null : false,
            'extended_score'    => tirreno('models')->userScore->getScoreDetailsByUserId($userId, $apiKey, true),
        ];
    }

    public function getUserById(int $accountId, int $apiKey): array {
        $user = tirreno('models')->user->getUserById($accountId, $apiKey);
        $rules = tirreno('models')->rules->getAll();

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
        $user = tirreno('utils')->timezones->localizeTimestampsForActiveOperator($tsColumns, $user);

        return $user;
    }

    public function checkIfOperatorHasAccess(int $userId, int $apiKey): bool {
        return tirreno('models')->user->checkAccess($userId, $apiKey);
    }

    public function checkEnrichmentAvailability(): bool {
        return tirreno('utils')->apiKeys->getCurrentOperatorEnrichmentKeyString() !== null;
    }

    public function addToWatchlist(int $accountId, int $apiKey): void {
        tirreno('models')->watchlist->add($accountId, $apiKey);
    }

    public function removeFromWatchlist(int $accountId, int $apiKey): void {
        tirreno('models')->watchlist->remove($accountId, $apiKey);
    }

    public function addToReviewQueue(int $accountId, int $apiKey): void {
        tirreno('models')->user->addToReviewQueue($accountId, $apiKey);
        tirreno('controllers')->reviewQueue->setNotReviewedCount(false, $apiKey);
    }

    public function addToBlacklistQueue(int $accountId, bool $fraud, bool $cron, bool $cnt, int $apiKey): void {
        $inQueue = tirreno('models')->queue->isInQueue($accountId, tirreno('utils')->constants->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey);

        if (!$fraud) {
            $this->setFraudFlag($accountId, false, $apiKey); // Directly remove blacklisted items

            if ($inQueue) {
                tirreno('models')->queue->removeFromQueue($accountId, tirreno('utils')->constants->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey); // Cancel queued operation
            }
        }

        if (!$inQueue && $fraud) {
            tirreno('models')->queue->add($accountId, tirreno('utils')->constants->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey);
        }

        tirreno('models')->user->updateFraudFlag([$accountId], $apiKey, $fraud);

        if ($cnt) {
            tirreno('controllers')->blacklist->setBlacklistUsersCount(false, $apiKey);      // do not use cache
            tirreno('controllers')->reviewQueue->setNotReviewedCount(false, $apiKey);       // do not use cache
        }

        if (!$cron) {
            $this->setReviewedFlag($accountId, true, $apiKey);
        }

        tirreno('utils')->routes->callExtra('UPDATE_USER_FRAUD_STATUS', $accountId, $fraud, $cron, $apiKey);
    }

    /**
     * @param array{accountId: int, key: int}[] $accounts
     */
    public function addBatchToCalculateRiskScoreQueue(array $accounts): void {
        tirreno('models')->queue->addBatch($accounts, tirreno('utils')->constants->RISK_SCORE_QUEUE_ACTION_TYPE);
    }

    public function setReviewedFlag(int $accountId, bool $reviewed, int $apiKey): void {
        tirreno('models')->user->updateReviewedFlag($accountId, $reviewed, $apiKey);
    }

    public function getUserScore(int $accountId, int $apiKey): array {
        $total = 0;
        $rules = [];

        tirreno('controllers')->rules->evaluateUser($accountId, $apiKey);

        $rules = tirreno('models')->user->getApplicableRulesByAccountId($accountId, $apiKey);

        $total = $rules[0]['total_score'] ?? 0;
        array_walk($rules, function (&$rule): void {
            unset($rule['total_score']);
        }, $rules);

        return [$total, $rules];
    }

    public function getScheduledForDeletion(int $userId, int $apiKey): array {
        [$scheduled, $status] = tirreno('models')->queue->isInQueueStatus($userId, tirreno('utils')->constants->DELETE_USER_QUEUE_ACTION_TYPE, $apiKey);

        return [$scheduled, ($status === tirreno('utils')->constants->FAILED_QUEUE_STATUS_TYPE) ? tirreno('utils')->errorCodes->USER_DELETION_FAILED : null];
    }

    public function getScheduledForBlacklist(int $userId, int $apiKey): array {
        [$scheduled, $status] = tirreno('models')->queue->isInQueueStatus($userId, tirreno('utils')->constants->BLACKLIST_QUEUE_ACTION_TYPE, $apiKey);

        return [$scheduled, ($status === tirreno('utils')->constants->FAILED_QUEUE_STATUS_TYPE) ? tirreno('utils')->errorCodes->USER_BLACKLISTING_FAILED : null];
    }

    public function setFraudFlag(int $accountId, bool $fraud, int $apiKey): array {
        $ips    = tirreno('models')->blacklistItems->getIpsRelatedToAccountWithinOperator($accountId, $apiKey);
        $emails = tirreno('models')->blacklistItems->getEmailsRelatedToAccountWithinOperator($accountId, $apiKey);
        $phones = tirreno('models')->blacklistItems->getPhonesRelatedToAccountWithinOperator($accountId, $apiKey);

        $relatedIpsIds = array_column($ips, 'id');
        $relatedEmailsIds = array_column($emails, 'id');
        $relatedPhonesIds = array_column($phones, 'id');

        $ips = tirreno('models')->blacklistItems->getIpsRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedIpsIds = array_column($ips, 'id');
        if (count($relatedIpsIds) !== 0) {
            tirreno('models')->ip->updateFraudFlag($relatedIpsIds, $fraud, $apiKey);
        }

        $emails = tirreno('models')->blacklistItems->getEmailsRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedEmailsIds = array_column($emails, 'id');
        if (count($relatedEmailsIds) !== 0) {
            tirreno('models')->email->updateFraudFlag($relatedEmailsIds, $fraud, $apiKey);
        }

        $phones = tirreno('models')->blacklistItems->getPhonesRelatedToAccountWithinOperator($accountId, $apiKey);
        $relatedPhonesIds = array_column($phones, 'id');
        if (count($relatedPhonesIds) !== 0) {
            tirreno('models')->phone->updateFraudFlag($relatedPhonesIds, $fraud, $apiKey);
        }

        return array_merge($ips, $emails, $phones);
    }

    public function updateUserStatus(int $score, string $details, bool $cron, int $accountId, int $apiKey): void {
        $key = tirreno('models')->apiKeys->getKeyById($apiKey);
        $user = tirreno('models')->user->getUserById($accountId, $apiKey);

        $addToReview = $user['added_to_review'] === null && $user['fraud'] === null && $score <= $key['review_queue_threshold'];

        // update user score before blacklist processing
        tirreno('models')->user->updateUserStatus($score, $details, $addToReview, $accountId, $apiKey);

        if ($score <= $key['blacklist_threshold']) {
            $this->addToBlacklistQueue($accountId, true, true, false, $apiKey); // automatic blacklist anyway, do not recalculate
        } elseif (!$cron && $addToReview) {
            tirreno('controllers')->reviewQueue->setNotReviewedCount(false, $apiKey);           // do not use cache
        }

        tirreno('utils')->routes->callExtra('UPDATE_USER_STATUS', $score, $details, $addToReview, $cron, $accountId, $apiKey);
    }

    // only for event_account_score
    public function updateUserScore(array $scores, array $details, int $accountId, int $apiKey): void {
        tirreno('models')->userScore->updateUserScore($scores, $details, $accountId, $apiKey);
    }
}
