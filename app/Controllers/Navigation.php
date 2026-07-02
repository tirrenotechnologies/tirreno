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

namespace Tirreno\Controllers;

class Navigation {
    protected \Tirreno\Views\Base $response;

    //protected ?object $page = null;
    protected ?string $page = null;
    protected ?object $controller = null;
    protected ?\Tirreno\Entities\Operator $operator = null;
    protected ?int $apiKey = null;
    protected ?int $id = null;

    protected string $classname = '';
    protected int $timer;

    public function __construct() {
        $this->timer = tirreno('request')->setTimer();

        $keepSessionInDb = tirreno('storage')->get('KEEP_SESSION_IN_DB') ?? null;
        if (!tirreno('utils')->database->initConnect(boolval($keepSessionInDb))) {
            tirreno('response')->error(404);
        }

        //Determine current user
        tirreno('utils')->routes->setCurrentRequestOperator();
        tirreno('utils')->routes->setCurrentRequestApiKey();

        $this->operator = tirreno('utils')->routes->getCurrentRequestOperator();
        $this->apiKey = tirreno('utils')->routes->getCurrentRequestApiKey()?->id;

        tirreno('log')->debug('navigation construct for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($this->timer));
    }

    public function beforeroute(): void {
        tirreno('log')->debug('operator %s with roles %s accessing page %s', $this->operator->email, json_encode($this->operator->roles), tirreno('request')->getUri());

        $timer = tirreno('request')->setTimer();

        if (tirreno('request')->isAjax()) {
            tirreno('response')->error(403);
        }

        if ($this->operator->isLoggedIn()) {
            tirreno('utils')->updates->syncUpdates();

            if (!$this->apiKey) {
                tirreno('log')->debug('redirect to logout from route %s.', tirreno('request')->getUri());
                tirreno('response')->redirect('/logout');
            }

            $messages = tirreno('utils')->systemMessages->get($this->apiKey);

            tirreno('storage')->set('SYSTEM_MESSAGES', $messages);
        }

        tirreno('log')->debug('navigation beforeroute for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($timer));
    }

    public function afterroute(): void {
        $timer = tirreno('request')->setTimer();

        $shouldPrintSqlToLog = tirreno('storage')->get('PRINT_SQL_LOG_AFTER_EACH_SCRIPT_CALL');

        if ($shouldPrintSqlToLog) {
            $log = tirreno('utils')->database->getDb()->log();
            if ($log) {
                tirreno('utils')->logger->logSql(tirreno('request')->getPath(), $log);
            }
        }

        echo $this->response->render();

        tirreno('log')->debug('navigation afterroute for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($timer));
        tirreno('log')->debug('whole route processing for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($this->timer));
    }

    public function getSignupPage(): void {
        $this->response = tirreno('pages')->signup->showIndexPage();
    }

    public function getLoginPage(): void {
        $this->response = tirreno('pages')->login->showIndexPage();
    }

    public function getLogoutPage(): void {
        $this->response = tirreno('pages')->logout->showIndexPage();
    }

    public function getForgotPasswordPage(): void {
        $this->response = tirreno('pages')->forgotPassword->showIndexPage();
    }

    public function getPassworRecoveringPage(): void {
        $this->response = tirreno('pages')->passwordRecovering->showIndexPage();
    }

    public function getHomePage(): void {
        $this->response = tirreno('pages')->dashboard->showIndexPage();
    }

    public function getEventsPage(): void {
        $this->response = tirreno('pages')->events->showIndexPage();
    }

    public function getReviewQueuePage(): void {
        $this->response = tirreno('pages')->reviewQueue->showIndexPage();
    }

    public function getBlacklistPage(): void {
        $this->response = tirreno('pages')->blacklist->showIndexPage();
    }

    public function getLogbookPage(): void {
        $this->response = tirreno('pages')->logbook->showIndexPage();
    }

    public function getWatchlistPage(): void {
        $this->response = tirreno('pages')->watchlist->showIndexPage();
    }

    public function getApiPage(): void {
        $this->response = tirreno('pages')->api->showIndexPage();
    }

    public function getRulesPage(): void {
        $this->response = tirreno('pages')->rules->showIndexPage();
    }

    public function getSettingsPage(): void {
        $this->response = tirreno('pages')->settings->showIndexPage();
    }

    public function getManualCheckPage(): void {
        $this->response = tirreno('pages')->manualCheck->showIndexPage();
    }

    public function getUserPage(): void {
        $this->response = tirreno('pages')->user->showIndexPage();
    }

    public function getUserAgentPage(): void {
        $this->response = tirreno('pages')->userAgent->showIndexPage();
    }

    public function getIpPage(): void {
        $this->response = tirreno('pages')->ip->showIndexPage();
    }

    public function getDomainPage(): void {
        $this->response = tirreno('pages')->domain->showIndexPage();
    }

    public function getUsersPage(): void {
        $this->response = tirreno('pages')->users->showIndexPage();
    }

    public function getUserAgentsPage(): void {
        $this->response = tirreno('pages')->userAgents->showIndexPage();
    }

    public function getIpsPage(): void {
        $this->response = tirreno('pages')->ips->showIndexPage();
    }

    public function getIspsPage(): void {
        $this->response = tirreno('pages')->isps->showIndexPage();
    }

    public function getIspPage(): void {
        $this->response = tirreno('pages')->isp->showIndexPage();
    }

    public function getCountryPage(): void {
        $this->response = tirreno('pages')->country->showIndexPage();
    }

    public function getCountriesPage(): void {
        $this->response = tirreno('pages')->countries->showIndexPage();
    }

    public function getDomainsPage(): void {
        $this->response = tirreno('pages')->domains->showIndexPage();
    }

    public function getResourcesPage(): void {
        $this->response = tirreno('pages')->resources->showIndexPage();
    }

    public function getResourcePage(): void {
        $this->response = tirreno('pages')->resource->showIndexPage();
    }

    public function getFieldAuditsPage(): void {
        $this->response = tirreno('pages')->fields->showIndexPage();
    }

    public function getFieldAuditPage(): void {
        $this->response = tirreno('pages')->field->showIndexPage();
    }
}
