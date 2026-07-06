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

namespace Tirreno\Utils;

class Render {
    // TODO:
    // tirreno('utils')->dictManager->load($page);
    // sprintf('%s_page_title', $page)
    // sprintf('%s_breadcrumb_title', $page)

    public static function getUserScoreClass(?int $score, ?bool $fraud, ?string $addedToReview): array {
        if ($fraud !== null) {
            return $fraud ? ['&times;', 'low'] : ['OK', 'high'];
        }

        $cls = 'empty';

        if ($score !== null && $score >= tirreno('utils')->constants->USER_LOW_SCORE_INF && $score < tirreno('utils')->constants->USER_LOW_SCORE_SUP) {
            $cls = 'low';
        }

        if ($score !== null && $score >= tirreno('utils')->constants->USER_MEDIUM_SCORE_INF && $score < tirreno('utils')->constants->USER_MEDIUM_SCORE_SUP) {
            $cls = 'medium';
        }

        if ($score !== null && $score >= tirreno('utils')->constants->USER_HIGH_SCORE_INF) {
            $cls = 'high';
        }

        $score = $addedToReview ? '!' : $score;
        $score = $score === null ? '&minus;' : $score;

        return [$score, $cls];
    }

    public static function extractRequestParams(array $params): array {
        $result = [];

        foreach ($params as $key) {
            $result[$key] = tirreno('request')->getRequestParam($key);
        }

        return $result;
    }

    protected static function getPageTitle(string $page): string {
        $title = tirreno('storage')->get(sprintf('%s_page_title', $page));

        return static::getInternalPageTitleWithPostfix($title);
    }

    public static function getInternalPageTitleWithPostfix(string $title): string {
        $title = $title ? $title : tirreno('utils')->constants->UNAUTHORIZED_USERID;
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $title = sprintf('%s %s', $safeTitle, tirreno('utils')->constants->PAGE_TITLE_POSTFIX);

        return $title;
    }

    public static function getBreadcrumbTitle(string $page): string {
        return tirreno('storage')->get(sprintf('%s_breadcrumb_title', $page)) ?? '';
    }

    public static function applyPageParams(array $params, string $page): array {
        $time = gmdate('Y-m-d H:i:s');
        $time = tirreno('utils')->timezones->localizeForActiveOperator($time);

        $errorCode = $params['ERROR_CODE'] ?? null;
        $successCode = $params['SUCCESS_CODE'] ?? null;

        if (!isset($params['PAGE_TITLE'])) {
            $params['PAGE_TITLE'] = static::getPageTitle($page);
        }

        $params['BREADCRUMB_TITLE'] = static::getBreadcrumbTitle($page);
        $params['CURRENT_PATH'] = tirreno('request')->getPath();
        $params['CURRENT_PATTERN'] = tirreno('request')->getPattern();
        $params['SEARCH_PLACEHOLDER'] = tirreno('storage')->get(sprintf('%s_search_placeholder', $page)) ?? '';

        if ($errorCode) {
            $errorI18nCode = sprintf('error_%s', $errorCode);
            $errorMessage = tirreno('storage')->get($errorI18nCode);
            $params['ERROR_MESSAGE'] = $errorMessage;
        }

        if ($successCode) {
            $successI18nCode = sprintf('error_%s', $successCode);
            $successMessage = tirreno('storage')->get($successI18nCode);
            $params['SUCCESS_MESSAGE'] = $successMessage;
        }

        if (array_key_exists('ERROR_MESSAGE', $params)) {
            $params['ERROR_MESSAGE_TIMESTAMP'] = $time;
        }

        if (array_key_exists('SUCCESS_MESSAGE', $params)) {
            $params['SUCCESS_MESSAGE_TIMESTAMP'] = $time;
        }

        $currentOperator = tirreno('utils')->routes->getCurrentRequestOperator();
        if ($currentOperator->isLoggedIn()) {
            $cnt = $currentOperator->reviewQueueCnt ?? 0;
            $params['NUMBER_OF_NOT_REVIEWED_USERS'] = tirreno('utils')->conversion->formatKiloValue($cnt);

            $cnt = $currentOperator->blacklistUsersCnt ?? 0;
            $params['NUMBER_OF_BLACKLIST_USERS'] = tirreno('utils')->conversion->formatKiloValue($cnt);

            $params += tirreno('controllers')->main->getCurrentTime($currentOperator);
        }

        $params['ALLOW_EMAIL_PHONE'] = tirreno('utils')->variables->getEmailPhoneAllowed();
        $params['FILE_PAGES'] = tirreno('assets')->pages->getMenuPages();
        $params['USE_TEMPLATING_SUBDIR'] = true;

        tirreno('utils')->dictManager->load($page);

        $code = tirreno('session')->get('extra_message_code');
        if ($code !== null) {
            tirreno('session')->remove('extra_message_code');

            if (!isset($params['SYSTEM_MESSAGES'])) {
                $params['SYSTEM_MESSAGES'] = [];
            }

            $params['SYSTEM_MESSAGES'][] = [
                'text' => tirreno('storage')->get('error_' . $code),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $params['CSRF'] = tirreno('session')->get('csrf');

        return $params;
    }
}
