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

return array_merge(
    include 'Additional/Notifications.php',
    include 'Additional/Enrichment.php',
    include 'Additional/Totals.php',
    include 'Errors.php',
    include 'Parts/Welcome.php',
    include 'Parts/LeftMenu.php',
    include 'Parts/TopTen.php',
    include 'Parts/DetailsPanel.php',
    include 'Parts/UserDetails.php',
    include 'Parts/TimeZones.php',
    include 'Pages/Logout.php',
    include 'Pages/Login.php',
    include 'Pages/Signup.php',
    include 'Pages/ForgotPassword.php',
    include 'Pages/ChangeEmail.php',
    include 'Pages/PasswordRecovering.php',
    include 'Pages/AccountActivation.php',
    include 'Pages/Settings.php',
    include 'Pages/AdminHome.php',
    include 'Pages/Api.php',
    include 'Pages/BaseTable.php',
    include 'Pages/Blacklist.php',
    include 'Pages/Bots.php',
    include 'Pages/Countries.php',
    include 'Pages/Country.php',
    include 'Pages/Bot.php',
    include 'Pages/Bots.php',
    include 'Pages/Domain.php',
    include 'Pages/Domains.php',
    include 'Pages/Email.php',
    include 'Pages/Emails.php',
    include 'Pages/Events.php',
    include 'Pages/Ip.php',
    include 'Pages/Ips.php',
    include 'Pages/Isp.php',
    include 'Pages/Isps.php',
    include 'Pages/ManualCheck.php',
    include 'Pages/Phone.php',
    include 'Pages/Phones.php',
    include 'Pages/Profile.php',
    include 'Pages/Logbook.php',
    include 'Pages/Resource.php',
    include 'Pages/Resources.php',
    include 'Pages/RetentionPolicy.php',
    include 'Pages/ReviewQueue.php',
    include 'Pages/Rules.php',
    include 'Pages/TimeZone.php',
    include 'Pages/User.php',
    include 'Pages/Users.php',
    include 'Pages/Watchlist.php',
    include 'Pages/Devices.php',
);
