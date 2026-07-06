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

abstract class Base {
    protected \Tirreno\Views\Base $response;

    protected string $page;
    protected ?object $controller = null;
    protected \Tirreno\Entities\Operator $operator;
    protected ?int $apiKey = null;
    protected ?int $id = null;
    protected bool $allowGuest = false;

    protected string $classname = '';

    public function __construct() {
        $timer = tirreno('request')->setTimer();

        $keepSessionInDb = tirreno('storage')->get('KEEP_SESSION_IN_DB') ?? null;
        if (!tirreno('utils')->database->initConnect(boolval($keepSessionInDb))) {
            tirreno('response')->error(404);
        }

        //Determine current user
        tirreno('utils')->routes->setCurrentRequestOperator();
        tirreno('utils')->routes->setCurrentRequestApiKey();

        $this->operator     = tirreno('utils')->routes->getCurrentRequestOperator();
        $this->apiKey       = tirreno('utils')->apiKeys->getCurrentOperatorApiKeyId();
        $this->id           = tirreno('utils')->conversion->getIntRequestParam('id', true);

        $parts = explode('\\', static::class);
        $this->classname = $parts[count($parts) - 1];

        //$this->page         = tirreno('pages')->getByClassName($this->classname);
        $this->controller   = tirreno('controllers')->getByClassName($this->classname);

        if (!tirreno('session')->get('csrf')) {
            // Set anti-CSRF token.
            tirreno('session')->set('csrf', bin2hex(random_bytes(16)));
        }

        tirreno('storage')->set('CSRF', tirreno('session')->get('csrf'));
        tirreno('utils')->routes->callExtra('PAGE_BASE');

        if (!$this->isAllowed()) {
            $this->notAllowed();
        }

        tirreno('log')->debug('page %s construct finished in %f.', static::class, tirreno('request')->getTimer($timer));
    }

    protected function isAllowed(): bool {
        return ($this->allowGuest && $this->operator->isGuest()) || (!$this->allowGuest && !$this->operator->isGuest());
    }

    protected function notAllowed(): void {
        if (tirreno('request')->isAjax()) {
            tirreno('response')->error(404);
        }

        if (!$this->allowGuest && $this->operator->isGuest()) {
            tirreno('response')->redirect('/login');
        }

        if ($this->allowGuest && !$this->operator->isGuest()) {
            tirreno('response')->redirect('/');
        }
    }

    public function showIndexPage(): \Tirreno\Views\Frontend {
        $response = new \Tirreno\Views\Frontend();
        $response->data = [];

        if ($this->page) {
            $response->data = tirreno('utils')->render->applyPageParams($this->getPageParams(), $this->page);
        }

        return $response;
    }

    protected function getPageParams(): array {
        return [];
    }

    public function assertCanView(): void {
        if (!$this->operator->viewable($this->page)) {
            tirreno('response')->error(403);
        }
    }

    public function assertCanEdit(): void {
        if (!$this->operator->editable($this->page)) {
            tirreno('response')->error(403);
        }
    }

    public function assertCanDelete(): void {
        if (!$this->operator->deleteable($this->page)) {
            tirreno('response')->error(403);
        }
    }

    public function assertCanPublish(): void {
        if (!$this->operator->publishable($this->page)) {
            tirreno('response')->error(403);
        }
    }

    public function assertCanAdmin(): void {
        if (!$this->operator->adminable($this->page)) {
            tirreno('response')->error(403);
        }
    }
}
