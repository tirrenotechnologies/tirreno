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

class Data extends \Controllers\Base {
    // POST requests
    public function enrichEntity(): array {
        $controller = new \Controllers\Admin\Enrichment\Navigation();

        return $controller->enrichEntity();
    }

    public function saveRule(): array {
        $controller = new \Controllers\Admin\Rules\Navigation();

        return $controller->saveRule();
    }

    public function removeFromBlacklist(): array {
        $controller = new \Controllers\Admin\Blacklist\Navigation();

        return $controller->removeItemFromList();
    }

    public function removeFromWatchlist(): array {
        $controller = new \Controllers\Admin\Watchlist\Navigation();

        return $controller->removeUserFromList();
    }

    public function manageUser(): array {
        $controller = new \Controllers\Admin\User\Navigation();

        return $controller->manageUser();
    }

    // GET requests
    public function checkRule(): array {
        $controller = new \Controllers\Admin\Rules\Navigation();

        return $controller->checkRule();
    }

    public function getTimeFrameTotal(): array {
        $controller = new \Controllers\Admin\Totals\Navigation();

        return $controller->getTimeFrameTotal();
    }

    public function getCountries(): array {
        $controller = new \Controllers\Admin\Countries\Navigation();

        return $controller->getList();
    }

    public function getMap(): array {
        $controller = new \Controllers\Admin\Countries\Navigation();

        return $controller->getMap();
    }

    public function getIps(): array {
        $controller = new \Controllers\Admin\IPs\Navigation();

        return $controller->getList();
    }

    public function getEvents(): array {
        $controller = new \Controllers\Admin\Events\Navigation();

        return $controller->getList();
    }

    public function getLogbook(): array {
        $controller = new \Controllers\Admin\Logbook\Navigation();

        return $controller->getList();
    }

    public function getUsers(): array {
        $controller = new \Controllers\Admin\Users\Navigation();

        return $controller->getList();
    }

    public function getBots(): array {
        $controller = new \Controllers\Admin\Bots\Navigation();

        return $controller->getList();
    }

    public function getDevices(): array {
        $controller = new \Controllers\Admin\Devices\Navigation();

        return $controller->getList();
    }

    public function getResources(): array {
        $controller = new \Controllers\Admin\Resources\Navigation();

        return $controller->getList();
    }

    public function getDashboardStat(): array {
        $controller = new \Controllers\Admin\Home\Navigation();

        return $controller->getDashboardStat();
    }

    public function getTopTen(): array {
        $controller = new \Controllers\Admin\Home\Navigation();

        return $controller->getTopTen();
    }

    public function getChart(): array {
        $controller = new \Controllers\Admin\Home\Navigation();

        return $controller->getChart();
    }

    public function getEventDetails(): array {
        $controller = new \Controllers\Admin\Events\Navigation();

        return $controller->getEventDetails();
    }

    public function getLogbookDetails(): array {
        $controller = new \Controllers\Admin\Logbook\Navigation();

        return $controller->getLogbookDetails();
    }

    public function getEmailDetails(): array {
        $controller = new \Controllers\Admin\Emails\Navigation();

        return $controller->getEmailDetails();
    }

    public function getPhoneDetails(): array {
        $controller = new \Controllers\Admin\Phones\Navigation();

        return $controller->getPhoneDetails();
    }

    public function getUserDetails(): array {
        $controller = new \Controllers\Admin\UserDetails\Navigation();

        return $controller->getUserDetails();
    }

    public function getUserEnrichmentDetails(): array {
        $controller = new \Controllers\Admin\UserDetails\Navigation();

        return $controller->getUserEnrichmentDetails();
    }

    public function getNotCheckedEntitiesCount(): array {
        $controller = new \Controllers\Admin\Enrichment\Navigation();

        return $controller->getNotCheckedEntitiesCount();
    }

    public function getEmails(): array {
        $controller = new \Controllers\Admin\Emails\Navigation();

        return $controller->getList();
    }

    public function getPhones(): array {
        $controller = new \Controllers\Admin\Phones\Navigation();

        return $controller->getList();
    }

    public function getUserScoreDetails(): array {
        $controller = new \Controllers\Admin\User\Navigation();

        return $controller->getUserScoreDetails();
    }

    public function getIsps(): array {
        $controller = new \Controllers\Admin\ISPs\Navigation();

        return $controller->getList();
    }

    public function getDomains(): array {
        $controller = new \Controllers\Admin\Domains\Navigation();

        return $controller->getList();
    }

    public function getReviewUsersQueue(): array {
        $controller = new \Controllers\Admin\ReviewQueue\Navigation();

        return $controller->getList();
    }

    public function getReviewUsersQueueCount(): array {
        $controller = new \Controllers\Admin\ReviewQueue\Navigation();

        return $controller->getNumberOfNotReviewedUsers(false, true);   // do not use cache, overall count
    }

    public function getIspDetails(): array {
        $controller = new \Controllers\Admin\ISP\Navigation();

        return $controller->getIspDetails();
    }

    public function getIpDetails(): array {
        $controller = new \Controllers\Admin\IP\Navigation();

        return $controller->getIpDetails();
    }

    public function getDeviceDetails(): array {
        $controller = new \Controllers\Admin\Devices\Navigation();

        return $controller->getDeviceDetails();
    }

    public function getBotDetails(): array {
        $controller = new \Controllers\Admin\Bot\Navigation();

        return $controller->getBotDetails();
    }

    public function getDomainDetails(): array {
        $controller = new \Controllers\Admin\Domain\Navigation();

        return $controller->getDomainDetails();
    }

    public function getSearchResults(): array {
        $controller = new \Controllers\Admin\Search\Navigation();

        return $controller->getSearchResults();
    }

    public function getBlacklist(): array {
        $controller = new \Controllers\Admin\Blacklist\Navigation();

        return $controller->getList();
    }

    public function getUsageStats(): array {
        $controller = new \Controllers\Admin\Api\Navigation();

        return $controller->getUsageStats();
    }
}
