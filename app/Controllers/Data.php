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

class Data {
    protected \Tirreno\Views\Base $response;

    //protected ?object $page = null;
    protected ?string $page = null;
    protected ?object $controller = null;
    protected ?\Tirreno\Entities\Operator $operator = null;
    protected ?int $apiKey = null;
    protected ?int $id = null;

    protected array $data = [];

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

        $parts = explode('\\', static::class);
        $this->classname = $parts[count($parts) - 1];

        tirreno('log')->debug('ajax navigation construct for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($this->timer));
    }

    public function beforeroute(): void {
        $operator     = tirreno('utils')->routes->getCurrentRequestOperator();
        tirreno('log')->debug('operator %s with roles %s accessing ajax %s', $operator->email, json_encode($operator->roles), tirreno('request')->getUri());

        $timer = tirreno('request')->setTimer();

        if (!tirreno('request')->isAjax()) {
            tirreno('response')->error(403);
        }

        $errorCode = tirreno('request')->validateCsrf();
        if ($errorCode) {
            tirreno('log')->info('ajax request with invalid CSRF %s.', tirreno('request')->getUri());
            tirreno('response')->error(403);
        }

        tirreno('response')->redirectNotLoggedIn();

        tirreno('log')->debug('ajax navigation beforeroute for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($timer));
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

        $response = new \Tirreno\Views\Json();
        $response->data = $this->data;

        echo $response->render();

        tirreno('log')->debug('ajax navigation afterroute for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($timer));
        tirreno('log')->debug('whole ajax route processing for %s in %f.', tirreno('request')->getUri(), tirreno('request')->getTimer($this->timer));
    }

    public function getEventsList(): void {
        $this->data = tirreno('pages')->events->getList();
    }

    public function getUsersList(): void {
        $this->data = tirreno('pages')->users->getList();
    }

    public function getReviewQueueList(): void {
        $this->data = tirreno('pages')->reviewQueue->getList();
    }

    public function getBlacklistList(): void {
        $this->data = tirreno('pages')->blacklist->getList();
    }

    public function getDevicesList(): void {
        $this->data = tirreno('pages')->devices->getList();
    }

    public function getUserAgentsList(): void {
        $this->data = tirreno('pages')->userAgents->getList();
    }

    public function getResourcesList(): void {
        $this->data = tirreno('pages')->resources->getList();
    }

    public function getPhonesList(): void {
        $this->data = tirreno('pages')->phones->getList();
    }

    public function getFieldAuditTrailList(): void {
        $this->data = tirreno('pages')->fields->getTrailList();
    }

    public function getFieldAuditsList(): void {
        $this->data = tirreno('pages')->fields->getList();
    }

    public function getIspsList(): void {
        $this->data = tirreno('pages')->isps->getList();
    }

    public function getIpsList(): void {
        $this->data = tirreno('pages')->ips->getList();
    }

    public function getEmailsList(): void {
        $this->data = tirreno('pages')->emails->getList();
    }

    public function getDomainsList(): void {
        $this->data = tirreno('pages')->domains->getList();
    }

    public function getLogbookList(): void {
        $this->data = tirreno('pages')->logbook->getList();
    }

    public function getUsageStats(): void {
        $this->data = tirreno('pages')->api->getUsageStats();
    }

    public function getCountriesList(): void {
        $this->data = tirreno('pages')->countries->getList();
    }

    public function getRulesList(): void {
        $this->data = tirreno('pages')->rules->getList();
    }

    public function getEventsChart(): void {
        $this->data = tirreno('pages')->events->getChart();
    }

    public function getUsersChart(): void {
        $this->data = tirreno('pages')->users->getChart();
    }

    public function getReviewQueueChart(): void {
        $this->data = tirreno('pages')->reviewQueue->getChart();
    }

    public function getBlacklistChart(): void {
        $this->data = tirreno('pages')->blacklist->getChart();
    }

    public function getDevicesChart(): void {
        $this->data = tirreno('pages')->devices->getChart();
    }

    public function getUserAgentsChart(): void {
        $this->data = tirreno('pages')->userAgents->getChart();
    }

    public function getResourcesChart(): void {
        $this->data = tirreno('pages')->resources->getChart();
    }

    public function getPhonesChart(): void {
        $this->data = tirreno('pages')->phones->getChart();
    }

    public function getFieldAuditsChart(): void {
        $this->data = tirreno('pages')->fields->getChart();
    }

    public function getIspsChart(): void {
        $this->data = tirreno('pages')->isps->getChart();
    }

    public function getIpsChart(): void {
        $this->data = tirreno('pages')->ips->getChart();
    }

    public function getDomainsChart(): void {
        $this->data = tirreno('pages')->domains->getChart();
    }

    public function getLogbookChart(): void {
        $this->data = tirreno('pages')->logbook->getChart();
    }

    public function getUserSparklinesChart(): void {
        $this->data = tirreno('pages')->user->getSparklinesChart();
    }

    public function getIpsTimeFrameTotal(): void {
        $this->data = tirreno('pages')->ips->getTimeFrameTotal();
    }

    public function getIspsTimeFrameTotal(): void {
        $this->data = tirreno('pages')->isps->getTimeFrameTotal();
    }

    public function getDomainsTimeFrameTotal(): void {
        $this->data = tirreno('pages')->domains->getTimeFrameTotal();
    }

    public function getCountriesTimeFrameTotal(): void {
        $this->data = tirreno('pages')->countries->getTimeFrameTotal();
    }

    public function getResourcesTimeFrameTotal(): void {
        $this->data = tirreno('pages')->resources->getTimeFrameTotal();
    }

    public function getFieldAuditsTimeFrameTotal(): void {
        $this->data = tirreno('pages')->fields->getTimeFrameTotal();
    }

    public function getUserAgentsTimeFrameTotal(): void {
        $this->data = tirreno('pages')->userAgents->getTimeFrameTotal();
    }

    public function getEmailDetails(): void {
        $this->data = tirreno('pages')->emails->getEmailDetails();
    }

    public function getEventDetails(): void {
        $this->data = tirreno('pages')->events->getEventDetails();
    }

    public function getFieldEventDetails(): void {
        $this->data = tirreno('pages')->fields->getFieldEventDetails();
    }

    public function getPhoneDetails(): void {
        $this->data = tirreno('pages')->phones->getPhoneDetails();
    }

    public function getDeviceDetails(): void {
        $this->data = tirreno('pages')->devices->getDeviceDetails();
    }

    public function getLogbookDetails(): void {
        $this->data = tirreno('pages')->logbook->getLogbookDetails();
    }

    public function getNotCheckedEntitiesCount(): void {
        $this->data = tirreno('pages')->api->getNotCheckedEntitiesCount();
    }

    public function getDomainDetails(): void {
        $this->data = tirreno('pages')->domain->getDomainDetails();
    }

    public function getUserAgentDetails(): void {
        $this->data = tirreno('pages')->userAgent->getUserAgentDetails();
    }

    public function getIspDetails(): void {
        $this->data = tirreno('pages')->isp->getIspDetails();
    }

    public function getIpDetails(): void {
        $this->data = tirreno('pages')->ip->getIpDetails();
    }

    public function getUserDetails(): void {
        $this->data = tirreno('pages')->user->getUserDetails();
    }

    public function saveRule(): void {
        $this->data = tirreno('pages')->rules->saveRule();
    }

    public function removeFromBlacklist(): void {
        $this->data = tirreno('pages')->blacklist->removeFromBlacklist();
    }

    public function removeFromWatchlist(): void {
        $this->data = tirreno('pages')->watchlist->removeFromWatchlist();
    }

    public function enrichPhoneEntity(): void {
        $this->data = tirreno('pages')->phones->enrichEntity();
    }

    public function enrichEmailEntity(): void {
        $this->data = tirreno('pages')->emails->enrichEntity();
    }

    public function manageUser(): void {
        $this->data = tirreno('pages')->user->manageUser();
    }

    public function reviewUser(): void {
        $this->data = tirreno('pages')->reviewQueue->reviewUser();
    }

    public function getTopTen(): void {
        $this->data = tirreno('pages')->dashboard->getTopTen();
    }

    public function getCurrentTime(): void {
        $this->data = tirreno('pages')->main->getCurrentTime();
    }

    public function getConstants(): void {
        $this->data = tirreno('pages')->main->getConstants();
    }

    public function getSearchResults(): void {
        $this->data = tirreno('pages')->main->getSearchResults();
    }

    public function checkRule(): void {
        $this->data = tirreno('pages')->rules->checkRule();
    }

    public function getUserScoreDetails(): void {
        $this->data = tirreno('pages')->user->getUserScoreDetails();
    }

    public function getDashboardStat(): void {
        $this->data = tirreno('pages')->dashboard->getDashboardStat();
    }

    public function getMap(): void {
        $this->data = tirreno('pages')->countries->getMap();
    }

    public function getReviewUsersQueueCount(): void {
        $this->data = tirreno('pages')->reviewQueue->setNotReviewedCount(false);
    }

    public function getBlacklistUsersCount(): void {
        $this->data = tirreno('pages')->blacklist->setBlacklistUsersCount(false);
    }
}
