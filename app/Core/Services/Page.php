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

namespace Tirreno\Core\Services;

class Page {
    //private ?string $route = null;
    private ?string $name = null;
    private ?string $title = null;
    private ?string $template = null;
    private ?string $javascript = null;
    private bool $authenticated = true;
    private array $allowedRoles = [];
    private array $blockedRoles = [];
    private array $params = [];

    /*public function setRoute(string $val): void {
        $this->route = $val;
    }*/

    public function setName(string $val): void {
        $this->name = $val;
    }

    public function setTitle(string $val): void {
        $this->title = $val;
    }

    public function setJavascript(string $val): void {
        $this->javascript = $val;
    }

    public function setTemplate(string $val): void {
        $this->template = $val;
    }

    public function setAuthenticated(bool $val): void {
        $this->authenticated = $val;
    }

    public function setAllowedRoles(array $val): void {
        $verb = tirreno('request')->getRequestType();

        $this->allowedRoles = array_values($val) === $val ? $val : ($val[$verb] ?? []);
        $this->allowedRoles = $this->allowedRoles === [] ? ['operator'] : $this->allowedRoles;
    }

    public function setBlockedRoles(array $val): void {
        $verb = tirreno('request')->getRequestType();

        $this->blockedRoles = array_values($val) === $val ? $val : ($val[$verb] ?? []);
    }

    /*public function getRoute(): ?string {
        return $this->route;
    }*/

    public function setParams(array $params): void {
        $this->params = $params;
    }

    // array_merge -> second param overrides, `+` -> second param only appends new keys
    public function addParams(array $params): void {
        $this->params = array_merge($this->params, $params);
    }

    public function getName(): ?string {
        return $this->name ?? 'DefaultName';
    }

    public function getTitle(): ?string {
        return $this->title ?? 'Default title.';
    }

    public function getJavascript(): ?string {
        return $this->javascript;
    }

    public function getTemplate(): ?string {
        return $this->template;
    }

    public function getAuthenticated(): bool {
        return $this->authenticated;
    }

    public function getAllowedRoles(): array {
        return $this->allowedRoles;
    }

    public function getBlockedRoles(): array {
        return $this->blockedRoles;
    }

    public function getParams(): array {
        return $this->params;
    }
}
