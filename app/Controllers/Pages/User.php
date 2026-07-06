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

namespace Tirreno\Controllers\Pages;

class User extends \Tirreno\Controllers\Pages\Base {
    public string $page = 'user';

    protected function proceedPostRequest(int $apiKey): array {
        $this->assertCanEdit();

        $cmd = tirreno('utils')->conversion->getStringRequestParam('cmd');

        if ($cmd === 'delete') {
            $this->assertCanDelete();
        }

        return match ($cmd) {
            'riskScore'     => tirreno('controllers')->user->recalculateRiskScore($apiKey),
            'delete'        => tirreno('controllers')->user->deleteUser($apiKey),
            'reenrichment'  => tirreno('controllers')->enrichment->enrichEntityFromRequest($apiKey),
            default => []
        };
    }

    protected function getPageParams(): array {
        $this->assertCanView();

        $userId = tirreno('utils')->conversion->getIntUrlParam('userId');
        $hasAccess = tirreno('controllers')->user->checkIfOperatorHasAccess($userId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $postParams = tirreno('request')->isPost() ? $this->proceedPostRequest($this->apiKey) : [];

        [$scheduledForDeletion, $errorCode] = tirreno('controllers')->user->getScheduledForDeletion($userId, $this->apiKey);
        $user = tirreno('controllers')->user->getUserById($userId, $this->apiKey);

        $pageTitle      = tirreno('utils')->render->getInternalPageTitleWithPostfix($user['page_title']);
        $enrichmentOn   = tirreno('controllers')->user->checkEnrichmentAvailability();

        $pageParams = [
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'HTML_FILE'                     => 'user.html',
            'LOAD_UPLOT'                    => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'USER'                          => $user,
            'SCHEDULED_FOR_DELETION'        => $scheduledForDeletion,
            'PAGE_TITLE'                    => $pageTitle,
            'ENRICHMENT'                    => $enrichmentOn,
            'JS'                            => 'user.js',
            'ERROR_CODE'                    => $errorCode,
            'SEARCH_PLACEHOLDER'            => tirreno('storage')->get('fieldAudits_search_placeholder'),
            'INTERNAL_PAGE'                 => true,
        ];

        [$scheduledForBlacklist, $errorCode] = tirreno('controllers')->user->getScheduledForBlacklist($userId, $this->apiKey);
        if ($scheduledForBlacklist) {
            tirreno('session')->set('extra_message_code', $errorCode ?? tirreno('utils')->errorCodes->USER_BLACKLISTING_QUEUED);
        }

        return array_merge($pageParams, $postParams);
    }

    public function manageUser(): array {
        $this->assertCanEdit();

        $timer = tirreno('request')->setTimer();
        $accountId  = tirreno('utils')->conversion->getIntRequestParam('userId');
        $cmd        = tirreno('utils')->conversion->getStringRequestParam('type');
        $hasAccess  = $this->controller->checkIfOperatorHasAccess($accountId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        $successCode = false;

        switch ($cmd) {
            case 'add':
                $this->controller->addToWatchlist($accountId, $this->apiKey);
                $successCode = tirreno('utils')->errorCodes->USER_ADDED_TO_WATCHLIST;
                break;

            case 'remove':
                $this->controller->removeFromWatchlist($accountId, $this->apiKey);
                $successCode = tirreno('utils')->errorCodes->USER_REMOVED_FROM_WATCHLIST;
                break;

            case 'fraud':
                $this->controller->addToBlacklistQueue($accountId, true, false, true, $this->apiKey);   // recalculate
                $successCode = tirreno('utils')->errorCodes->USER_FRAUD_FLAG_SET;
                break;

            case 'legit':
                $this->controller->addToBlacklistQueue($accountId, false, false, true, $this->apiKey);  // recalculate
                $successCode = tirreno('utils')->errorCodes->USER_FRAUD_FLAG_UNSET;
                break;

            case 'add-to-review':
                $this->controller->addToReviewQueue($accountId, $this->apiKey);     // set added_to_review = NOW() & fraud = null
                $successCode = tirreno('utils')->errorCodes->USER_ADDED_TO_REVIEW;
                break;
        }

        tirreno('log')->debug('complete manageUser() with command %s in %f.', $cmd, tirreno('request')->getTimer($timer));

        return  ['success' => $successCode];
    }

    public function getSparklinesChart(): array {
        $this->assertCanView();

        return $this->apiKey ? $this->controller->getSparklinesChart($this->apiKey) : [];
    }

    public function getUserScoreDetails(): array {
        $this->assertCanView();

        $userId = tirreno('utils')->conversion->getIntRequestParam('userId');

        return $this->controller->getUserScoreDetails($userId, $this->apiKey);
    }

    public function getUserDetails(): array {
        $this->assertCanView();

        $userId = tirreno('utils')->conversion->getIntRequestParam('userId');
        $hasAccess = $this->controller->checkIfOperatorHasAccess($userId, $this->apiKey);

        if (!$hasAccess) {
            tirreno('response')->error(404);
        }

        return $this->controller->getUserDetails($userId, $this->apiKey);
    }
}
