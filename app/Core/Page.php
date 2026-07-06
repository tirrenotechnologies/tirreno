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

namespace Tirreno\Core;

abstract class Page {
    protected \Tirreno\Views\Base $response;

    public function __construct() {
        $this->registerRoute();
    }

    abstract protected function init(): void;

    abstract protected function getRoute(): string;

    public function registerRoute(): void {
        $route = $this->getRoute();
        if ($route) {
            $routeDef = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS ' . $route;
            $routeDest = get_class($this) . '->index';
            tirreno('router')->route($routeDef, $routeDest);
        }
    }

    protected function uploadHelpers(): void {
        $path = 'assets/pages/' . lcfirst(tirreno('page')->getName());

        // load dictionary file if present
        $dictionary = dirname(__DIR__, 2) . $path . '/dictionary.php';
        if (file_exists($dictionary)) {
            $values = include $dictionary;

            if ($values !== false) {
                foreach ($values as $key => $value) {
                    tirreno('storage')->set($key, $value);
                }
            }
        }

        $path .= '/ui/';
        $ui = tirreno('storage')->get('UI');
        //tirreno('storage')->set('UI', $ui . ';' . $path . 'templates/');
        tirreno('storage')->set('UI', $ui . ';' . $path . 'templates/');

        // TODO: add dictionary management
        tirreno('storage')->set(tirreno('page')->getName() . '_page_title', tirreno('page')->getTitle());

        $this->registerRouteOverrides($path . 'js/', '.js', 'application/javascript');
        $this->registerRouteOverrides($path . 'css/', '.css', null);
        $this->registerRouteOverrides($path . 'images/', '.svg', null);
    }

    protected function registerRouteOverrides(string $dir, string $extension, ?string $contentType = null): void {
        if (!is_dir($dir)) {
            return;
        }

        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));

        $root = rtrim(realpath($dir), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $len = strlen($root);
        $errorCode = 404;
        foreach ($iter as $file) {
            if ($file->isFile() && $file->getExtension() === $extension) {
                $relative = substr($file->getPathname(), $len);
                $path = $dir . $relative;

                tirreno('request')->registerRoute('GET', '/ui/' . $relative, function () use ($path, $contentType, $errorCode) {
                    $file = $path;
                    if (file_exists($file)) {
                        $contentType = $contentType === null ? mime_content_type($file) : $contentType;
                        header('Content-Type: ' . $contentType);
                        readfile($file);
                    } else {
                        tirreno('response')->error($errorCode);
                    }
                });
            }
        }
    }

    // allow user to perform their own request type logic
    public function index(): void {
        tirreno('response')->error(403);
    }

    public function beforeroute(): void {
        if (tirreno('request')->isAjax()) {
            $this->response = new \Tirreno\Views\Json();

            if (!tirreno('db')->initConnection()) {
                tirreno('log')->info('exit due to database connection fail.');
                tirreno('response')->error(404);
            }
            tirreno('session')->extractCurrentOperator();

            tirreno('utils')->routes->callExtra('PAGE_BASE');

            $this->init();

            $errorCode = tirreno('request')->validateCsrf();
            if ($errorCode) {
                tirreno('log')->info('exit due to CSRF token mismatch.');
                tirreno('resonse')->error(403);
            }

            if (tirreno('page')->getAuthenticated()) {
                tirreno('response')->errorNotLoggedIn();
                tirreno('response')->errorImproperRole(tirreno('page')->getAllowedRoles(), tirreno('page')->getBlockedRoles());
            }

            return;
        }

        $this->init();

        if (!tirreno('session')->get('csrf')) {
            tirreno('session')->set('csrf', bin2hex(random_bytes(16)));
        }

        $this->response = new \Tirreno\Views\Frontend();
        $this->response->data = [];

        if (!tirreno('db')->initConnection()) {
            tirreno('log')->info('exit due to database connection fail.');
            tirreno('response')->error(404);
        }

        tirreno('session')->extractCurrentOperator();

        tirreno('utils')->routes->callExtra('PAGE_BASE');

        $verb = tirreno('request')->getRequestType();
        if (tirreno('request')->isCli()) {
            tirreno('log')->info('request is initiated from command line.');
            tirreno('response')->error(403);
        }

        if ($verb !== 'GET' && !tirreno('request')->validateCsrf()) {
            tirreno('log')->info('form provided invalid CSRF token.');
            tirreno('response')->error(403);
        }

        if (tirreno('page')->getAuthenticated()) {
            tirreno('response')->redirectNotLoggedIn();
            tirreno('response')->redirectImproperRole(tirreno('page')->getAllowedRoles(), tirreno('page')->getBlockedRoles());
        }

        if (tirreno('page')->getAuthenticated() && tirreno('session')->getCurrentOperator()->isLoggedIn()) {
            $key = tirreno('session')->getCurrentKey();

            if (!$key) {
                tirreno('log')->info('redirect to /logout due to empty current key.');
                tirreno('response')->redirect('/logout');
            }

            $messages = tirreno('utils')->systemMessages->get($key->id);

            tirreno('storage')->set('SYSTEM_MESSAGES', $messages);
        }

        tirreno('page')->addParams($this->baseParams());
    }

    public function afterroute(): void {
        if (!tirreno('request')->isAjax()) {
            $this->uploadHelpers();
            $this->updateAndSetParams();
        }

        $this->response->data = tirreno('page')->getParams();

        tirreno('log')->logSqlIfPossible();

        echo $this->response->render();
    }

    private function updateAndSetParams(): void {
        $data = tirreno('page')->getParams();

        $code = $data['ERROR_CODE'] ?? null;
        if ($code && is_int($code)) {
            $data['ERROR_MESSAGE'] = tirreno('storage')->get('error_' . strval($code));
        }

        $code = $data['SUCCESS_CODE'] ?? null;
        if ($code && is_int($code)) {
            $data['SUCCESS_MESSAGE'] = tirreno('storage')->get('error_' . strval($code));
        }

            $time = tirreno('utils')->nowUtc();
        if (array_key_exists('ERROR_MESSAGE', $data)) {
            $data['ERROR_MESSAGE_TIMESTAMP'] = $time;
        }

        if (array_key_exists('SUCCESS_MESSAGE', $data)) {
            $data['SUCCESS_MESSAGE_TIMESTAMP'] = $time;
        }

        $code = tirreno('session')->get('extra_message_code');
        if ($code !== null) {
            tirreno('session')->remove('extra_message_code');

            if (!isset($data['SYSTEM_MESSAGES'])) {
                $data['SYSTEM_MESSAGES'] = [];
            }

            $data['SYSTEM_MESSAGES'][] = [
                'text'          => tirreno('storage')->get('error_' . $code),
                'created_at'    => $time,
            ];
        }

        $data = tirreno('utils')->routes->callExtra('APPLY_PAGE_PARAMS', $data, tirreno('page')->getName()) ?? $data;

        tirreno('page')->setParams($data);
    }

    protected function baseParams(): array {
        $time = tirreno('utils')->nowForCurrentOperator();

        $title = tirreno('storage')->get(tirreno('page')->getName() . '_page_title') ?: tirreno('utils')->constants->UNAUTHORIZED_USERID;
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $pageTitle = $safeTitle . ' '  . tirreno('utils')->constants->PAGE_TITLE_POSTFIX;

        $operator = tirreno('session')->getCurrentOperator();

        $params = [
            'PAGE_TITLE'                    => $pageTitle,
            'BREADCRUMB_TITLE'              => tirreno('storage')->get(tirreno('page')->getName() . '_breadcrumb_title') ?? '',
            'CURRENT_PATH'                  => tirreno('request')->getPath(),
            'CURRENT_PATTERN'               => tirreno('request')->getPattern(),
            'ALLOW_EMAIL_PHONE'             => tirreno('utils')->variables->getEmailPhoneAllowed(),
            'CSRF'                          => tirreno('session')->get('csrf'),
            'NOT_REVIEWED_USERS_CNT'        => tirreno('utils')->conversion->formatKiloValue($operator->reviewQueueCnt ?? 0),
            'BLACKLIST_USERS_CNT'           => tirreno('utils')->conversion->formatKiloValue($operator->blacklistUsersCnt ?? 0),
            'FILE_PAGES'                    => tirreno('assets')->pages->getMenuPages(),
            'HTML_FILE'                     => tirreno('page')->getTemplate(),
            'JS'                            => tirreno('page')->getJavascript(),
            'INTERNAL_PAGE'                 => tirreno('page')->getAuthenticated(),
            'LOAD_ACCEPT_LANGUAGE_PARSER'   => true,
            'LOAD_AUTOCOMPLETE'             => true,
            'LOAD_CHOICES'                  => true,
            'LOAD_DATATABLE'                => true,
            'LOAD_JVECTORMAP'               => true,
            'LOAD_UPLOT'                    => true,
        ];

        if ($operator) {
            $cnt = $operator->reviewQueueCnt ?? 0;
            $params['NUMBER_OF_NOT_REVIEWED_USERS'] = tirreno('utils')->conversion->formatKiloValue($cnt);

            $cnt = $operator->blacklistUsersCnt ?? 0;
            $params['NUMBER_OF_BLACKLIST_USERS'] = tirreno('utils')->conversion->formatKiloValue($cnt);

            $params += tirreno('controllers')->main->getCurrentTime($operator);
        }

        return $params;
    }
}
