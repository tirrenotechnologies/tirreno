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

return [
    'home_page_title' => 'Dashboard',
    'home_header_title' => 'Dashboard',
    'home_breadcrumb_title' => 'Dashboard',

    'home_table_title' => 'Latest events',
    'home_table_column_timestamp' => 'Timestamp',
    'home_table_column_user_id' => 'Entity Id',
    'home_table_column_url' => 'URL',
    'home_table_column_client_ip' => 'Client IP',
    'home_table_column_country' => 'Country',

    'home_total_events' => 'Events',
    'home_total_events_tooltip' => 'The number of events during a selected period of time and in total.',
    'home_total_users' => 'Entities',
    'home_total_users_tooltip' => 'The number of active entities during a selected period of time and in total.',
    'home_total_ips' => 'IP addresses',
    'home_total_ips_tooltip' => 'The number of active IP addresses during a selected period of time and in total.',
    'home_total_countries' => 'Countries',
    'home_total_countries_tooltip' => 'The number of identified countries during a selected period of time and in total.',
    'home_total_urls' => 'Resources',
    'home_total_urls_tooltip' => 'The number of requested resources during a selected period of time and in total.',
    'home_total_users_for_review' => 'Review',
    'home_total_users_for_review_tooltip' => 'The number of entities added to the review queue during a selected period of time and in total.',
    'home_total_blocked_users' => 'Blacklisted',
    'home_total_blocked_users_tooltip' => 'The number of blacklisted entities during a selected period of time and in total.',
    'home_view_all' => 'View all',

    'home_top10_most_active_users' => 'Activity by entities',
    'home_top10_most_active_users_tooltip' => 'A list of entities with the highest quantity of recorded events.',
    'home_top10_active_countries' => 'Activity by countries',
    'home_top10_active_countries_tooltip' => 'A list of countries with the highest quantity of recorded entities.',
    'home_top10_active_urls' => 'Activity by resources',
    'home_top10_active_urls_tooltip' => 'A list of resources with the highest quantity of recorded entities.',
    'home_top10_ips_with_the_most_users' => 'Shared IP addresses',
    'home_top10_ips_with_the_most_users_tooltip' => 'A list of IP addresses utilized by several entities.',
    'home_top10_users_with_most_login_fail' => 'Account login fail',
    'home_top10_users_with_most_login_fail_tooltip' => 'A list of entities with the highest quantity of failed login attempts.',
    'home_top10_users_with_the_most_ips' => 'Multiple IP addresses',
    'home_top10_users_with_the_most_ips_tooltip' => 'A list of entities with a high number of IP addresses.',

    'home_clock_day_tooltip' => 'The day of year (DOY) is the sequential day number starting with day 1 on January 1st.',
    'home_clock_time_tooltip' => 'Current time in your application based on timezone settings.',
];
