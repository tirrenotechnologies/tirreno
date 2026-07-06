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

class FileBasedPage extends Page {
    protected ?string $routeName = null;
    protected ?string $filePath = null;
    protected ?string $name = null;

    public function __construct() {
        $url = tirreno('request')->getPath();
        $filename = explode('/', trim($url, '/'))[0];

        // if file exists -> register route and use this file in index
        $path = dirname(__DIR__, 2) . '/assets/pages/' . $filename . '.php';

        tirreno('log')->debug('lookup file %s for URI %s.', $path, $url);

        if ($filename && $filename !== 'index' && file_exists($path) && preg_match('/^[A-Za-z0-9_-]+$/', $filename)) {
            $this->name = $filename;
            $this->routeName = '/' . $filename;
            $this->filePath = $path;
        }

        parent::__construct();
    }

    protected function getRoute(): string {
        return $this->routeName ?? '';
    }

    public function index(): void {
        if ($this->filePath) {
            $session    = tirreno('session');
            $request    = tirreno('request');
            $response   = tirreno('response');
            $sysop      = tirreno('sysop');
            $utils      = tirreno('utils');
            $page       = tirreno('page');
            $helpers    = tirreno('helpers');
            $db         = tirreno('db');
            $log        = tirreno('log');
            $user       = tirreno('user');
            $ip         = tirreno('ip');

            include_once $this->filePath;
        }
    }

    protected function init(): void {
        tirreno('page')->setTemplate($this->name . '.html');
        //name, title, template, js, authentication and roles should be set in route file
    }

    protected function uploadHelpers(): void {
        $path = 'assets/pages/views/';
        $ui = tirreno('storage')->get('UI');
        tirreno('storage')->set('UI', $ui . ';' . $path);

        // TODO: add dictionary management
        //tirreno('storage')->set(tirreno('page')->getName() . '_page_title', tirreno('page')->getTitle());

        $this->registerRouteOverrides($path . 'js/', '.js', 'application/javascript');
        $this->registerRouteOverrides($path . 'css/', '.css', null);
        $this->registerRouteOverrides($path . 'images/', '.svg', null);
    }

    public function beforeroute(): void {
        if (!tirreno('db')->initConnection()) {
            tirreno('log')->info('exit due database connection fail.');
            tirreno('response')->error(404);
        }

        tirreno('session')->extractCurrentOperator();

        tirreno('utils')->routes->callExtra('PAGE_BASE');

        $this->init();

        if (!tirreno('session')->get('csrf')) {
            tirreno('session')->set('csrf', bin2hex(random_bytes(16)));
        }

        $this->response = tirreno('request')->isAjax() ? (new \Tirreno\Views\Json()) : (new \Tirreno\Views\Frontend());
        $this->response->data = [];

        if (tirreno('session')->getCurrentOperator()) {
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
}
