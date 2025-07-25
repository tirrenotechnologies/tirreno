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

namespace Controllers\Admin\Data;

class Navigation extends \Controllers\Base {
    use \Traits\Navigation;

    private $dataController;

    public function beforeroute(): void {
        $errorCode = $this->validateCsrfToken();
        if ($errorCode) {
            $this->f3->error(403);
        }

        $this->redirectIfUnlogged();

        $this->dataController = new Data();
        $this->response = new \Views\Json();
    }

    // POST requests
    public function enrichEntity(): void {
        $this->response->data = $this->dataController->enrichEntity();
    }

    public function manageUser(): void {
        $this->response->data = $this->dataController->manageUser();
    }

    public function removeFromWatchlist(): void {
        $this->response->data = $this->dataController->removeFromWatchlist();
    }

    public function removeFromBlacklist(): void {
        $this->response->data = $this->dataController->removeFromBlacklist();
    }

    public function saveRule(): void {
        $this->response->data = $this->dataController->saveRule();
    }

    // GET requests
    public function checkRule(): void {
        $this->response->data = $this->dataController->checkRule();
    }

    public function getTimeFrameTotal(): void {
        $this->response->data = $this->dataController->getTimeFrameTotal();
    }

    public function getCountries(): void {
        $this->response->data = $this->dataController->getCountries();
    }

    public function getMap(): void {
        $this->response->data = $this->dataController->getMap();
    }

    public function getIps(): void {
        $this->response->data = $this->dataController->getIps();
    }

    public function getEvents(): void {
        $this->response->data = $this->dataController->getEvents();
    }

    public function getLogbook(): void {
        $this->response->data = $this->dataController->getLogbook();
    }

    public function getUsers(): void {
        $this->response->data = $this->dataController->getUsers();
    }

    public function getBots(): void {
        $this->response->data = $this->dataController->getBots();
    }

    public function getDevices(): void {
        $this->response->data = $this->dataController->getDevices();
    }

    public function getResources(): void {
        $this->response->data = $this->dataController->getResources();
    }

    public function getTopTen(): void {
        $this->response->data = $this->dataController->getTopTen();
    }

    public function getDashboardStat(): void {
        $this->response->data = $this->dataController->getDashboardStat();
    }

    public function getChart(): void {
        $this->response->data = $this->dataController->getChart();
    }

    public function getEventDetails(): void {
        $this->response->data = $this->dataController->getEventDetails();
    }

    public function getLogbookDetails(): void {
        $this->response->data = $this->dataController->getLogbookDetails();
    }

    public function getEmailDetails(): void {
        $this->response->data = $this->dataController->getEmailDetails();
    }

    public function getPhoneDetails(): void {
        $this->response->data = $this->dataController->getPhoneDetails();
    }

    public function getUserDetails(): void {
        $this->response->data = $this->dataController->getUserDetails();
    }

    public function getUserEnrichmentDetails(): void {
        $this->response->data = $this->dataController->getUserEnrichmentDetails();
    }

    public function getNotCheckedEntitiesCount(): void {
        $this->response->data = $this->dataController->getNotCheckedEntitiesCount();
    }

    public function getEmails(): void {
        $this->response->data = $this->dataController->getEmails();
    }

    public function getPhones(): void {
        $this->response->data = $this->dataController->getPhones();
    }

    public function getUserScoreDetails(): void {
        $this->response->data = $this->dataController->getUserScoreDetails();
    }

    public function getIsps(): void {
        $this->response->data = $this->dataController->getIsps();
    }

    public function getDomains(): void {
        $this->response->data = $this->dataController->getDomains();
    }

    public function getIspDetails(): void {
        $this->response->data = $this->dataController->getIspDetails();
    }

    public function getIpDetails(): void {
        $this->response->data = $this->dataController->getIpDetails();
    }

    public function getDeviceDetails(): void {
        $this->response->data = $this->dataController->getDeviceDetails();
    }

    public function getBotDetails(): void {
        $this->response->data = $this->dataController->getBotDetails();
    }

    public function getDomainDetails(): void {
        $this->response->data = $this->dataController->getDomainDetails();
    }

    public function getReviewUsersQueue(): void {
        $this->response->data = $this->dataController->getReviewUsersQueue();
    }

    public function getReviewUsersQueueCount(): void {
        $this->response->data = $this->dataController->getReviewUsersQueueCount();
    }

    public function getSearchResults(): void {
        $this->response->data = $this->dataController->getSearchResults();
    }

    public function getBlacklist(): void {
        $this->response->data = $this->dataController->getBlacklist();
    }

    public function getUsageStats(): void {
        $this->response->data = $this->dataController->getUsageStats();
    }
}
