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

namespace Controllers\Pages;

abstract class Base {
    protected $f3;
    protected $page;

    public function __construct() {
        $this->f3 = \Base::instance();

        if (!$this->f3->exists('SESSION.csrf')) {
            // Set anti-CSRF token.
            $this->f3->set('SESSION.csrf', bin2hex(\openssl_random_pseudo_bytes(16)));
        }

        $this->f3->CSRF = $this->f3->get('SESSION.csrf');

        \Utils\Routes::callExtra('PAGE_BASE');
    }

    public function isPostRequest(): bool {
        return $this->f3->VERB === 'POST';
    }

    // TODO: reverse
    public function getPageTitle(): string {
        $title = $this->f3->get(sprintf('%s_page_title', $this->page));

        return $this->getInternalPageTitleWithPostfix($title);
    }

    public function getInternalPageTitleWithPostfix(string $title): string {
        $title = $title ? $title : \Utils\Constants::get('UNAUTHORIZED_USERID');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $title = sprintf('%s %s', $safeTitle, \Utils\Constants::get('PAGE_TITLE_POSTFIX'));

        return $title;
    }

    public function getBreadcrumbTitle(): string {
        $page = $this->page;
        $i18nKey = sprintf('%s_breadcrumb_title', $page);

        return $this->f3->get($i18nKey) ?? '';
    }

    public function applyPageParams(array $params): array {
        $time = gmdate('Y-m-d H:i:s');
        \Utils\TimeZones::localizeForActiveOperator($time);

        $errorCode = $params['ERROR_CODE'] ?? null;
        $successCode = $params['SUCCESS_CODE'] ?? null;

        if (!isset($params['PAGE_TITLE'])) {
            $pageTitle = $this->getPageTitle();
            $params['PAGE_TITLE'] = $pageTitle;
        }

        $breadCrumbTitle = $this->getBreadcrumbTitle();
        $params['BREADCRUMB_TITLE'] = $breadCrumbTitle;
        $params['CURRENT_PATH'] = $this->f3->PATH;
        $params['CURRENT_PATTERN'] = $this->f3->PATTERN;

        if ($errorCode) {
            $errorI18nCode = sprintf('error_%s', $errorCode);
            $errorMessage = $this->f3->get($errorI18nCode);
            $params['ERROR_MESSAGE'] = $errorMessage;
        }

        if ($successCode) {
            $successI18nCode = sprintf('error_%s', $successCode);
            $successMessage = $this->f3->get($successI18nCode);
            $params['SUCCESS_MESSAGE'] = $successMessage;
        }

        if (array_key_exists('ERROR_MESSAGE', $params)) {
            $params['ERROR_MESSAGE_TIMESTAMP'] = $time;
        }

        if (array_key_exists('SUCCESS_MESSAGE', $params)) {
            $params['SUCCESS_MESSAGE_TIMESTAMP'] = $time;
        }

        $currentOperator = \Utils\Routes::getCurrentRequestOperator();
        if ($currentOperator) {
            $cnt = $currentOperator->review_queue_cnt > 999 ? 999 : ($currentOperator->review_queue_cnt ?? 0);
            $params['NUMBER_OF_NOT_REVIEWED_USERS'] = $cnt;

            $cnt = $currentOperator->blacklist_users_cnt ?? 0;
            $params['NUMBER_OF_BLACKLIST_USERS'] = \Utils\Conversion::formatKiloValue($cnt);

            $offset = \Utils\TimeZones::getCurrentOperatorOffset();
            $now = time() + $offset;
            $day = \Utils\Constants::get('SECONDS_IN_DAY');
            $firstJan = mktime(0, 0, 0, 1, 1, intval(gmdate('Y')));

            $day = \Utils\Conversion::intVal(ceil(($now - $firstJan) / $day), 0);

            $params['OFFSET']   = $offset;
            $params['DAY']      = ($day < 10 ? '00' : ($day < 100 ? '0' : '')) . strval($day);
            $params['TIME_HIS'] = date('H:i:s', $now);
            $params['TIMEZONE'] = 'UTC' . (($offset < 0) ? '-' . date('H:i', -$offset) : '+' . date('H:i', $offset));
        }

        $params['ALLOW_EMAIL_PHONE'] = \Utils\Variables::getEmailPhoneAllowed();

        $page = $this->page;
        \Utils\DictManager::load($page);

        $code = $this->f3->get('SESSION.extra_message_code');
        if ($code !== null) {
            $this->f3->clear('SESSION.extra_message_code');

            if (!isset($params['SYSTEM_MESSAGES'])) {
                $params['SYSTEM_MESSAGES'] = [];
            }

            $params['SYSTEM_MESSAGES'][] = [
                'text' => $this->f3->get('error_' . $code),
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        $params = \Utils\Routes::callExtra('APPLY_PAGE_PARAMS', $params, $page) ?? $params;

        return $params;
    }

    protected function extractRequestParams(array $params): array {
        $result = [];

        foreach ($params as $key) {
            $result[$key] = \Base::instance()->get('REQUEST.' . $key);
        }

        return $result;
    }
}
