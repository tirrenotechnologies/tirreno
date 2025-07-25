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

namespace Controllers\Pages;

abstract class Base {
    use \Traits\Debug;
    use \Traits\ApiKeys;

    protected $f3;
    protected $page;

    public function __construct() {
        $this->f3 = \Base::instance();

        if (!$this->f3->exists('SESSION.csrf')) {
            // Set anti-CSRF token.
            $this->f3->set('SESSION.csrf', bin2hex(\openssl_random_pseudo_bytes(16)));
        }

        $this->f3->CSRF = $this->f3->get('SESSION.csrf');
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
        $errorCode = $params['ERROR_CODE'] ?? null;
        $successCode = $params['SUCCESS_CODE'] ?? null;

        if (!isset($params['PAGE_TITLE'])) {
            $pageTitle = $this->getPageTitle();
            $params['PAGE_TITLE'] = $pageTitle;
        }

        if ($this->f3->get('EXTRA_CSS')) {
            $params['EXTRA_CSS'] = $this->f3->get('EXTRA_CSS');
        }

        $breadCrumbTitle = $this->getBreadcrumbTitle();
        $params['BREADCRUMB_TITLE'] = $breadCrumbTitle;
        $params['CURRENT_PATH'] = $this->f3->PATH;

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
            $time = gmdate('Y-m-d H:i:s');
            \Utils\TimeZones::localizeForActiveOperator($time);
            $params['ERROR_MESSAGE_TIMESTAMP'] = $time;
        }

        if (array_key_exists('SUCCESS_MESSAGE', $params)) {
            $time = gmdate('Y-m-d H:i:s');
            \Utils\TimeZones::localizeForActiveOperator($time);
            $params['SUCCESS_MESSAGE_TIMESTAMP'] = $time;
        }

        $currentOperator = $this->f3->get('CURRENT_USER');
        if ($currentOperator) {
            $cnt = $currentOperator->review_queue_cnt > 999 ? 999 : ($currentOperator->review_queue_cnt ?? 0);
            $params['NUMBER_OF_NOT_REVIEWED_USERS'] = $cnt;

            $offset = \Utils\TimeZones::getCurrentOperatorOffset();
            $now = time() + $offset;
            $day = (int) ceil(($now - mktime(0, 0, 0, 1, 1, gmdate('Y'))) / (60 * 60 * 24));

            $params['OFFSET']   = $offset;
            $params['DAY']      = ($day < 10 ? '00' : ($day < 100 ? '0' : '')) . strval($day);
            $params['TIME']     = date('H:i:s', $now);
            $params['TIMEZONE'] = 'UTC' . (($offset < 0) ? '-' . date('H:i', -$offset) : '+' . date('H:i', $offset));
        }

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

        $extra = $this->f3->get('EXTRA_APPLY_PAGE_PARAMS');
        if ($extra && is_callable($extra)) {
            $params = $extra($params, $page);
        }

        return $params;
    }

    public function integerParam($param): int {
        $validated = filter_var($param, FILTER_VALIDATE_INT);

        return $validated !== false ? $validated : 0;
    }
}
