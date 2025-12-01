<?php

/**
 * tirreno ~ open security analytics
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
    'UserDetails_failed_login_count'            => 'Failed login',
    'UserDetails_failed_login_count_tooltip'    => 'Number of user\'s failed login attempts.',
    'UserDetails_password_reset_count'          => 'Password reset',
    'UserDetails_password_reset_count_tooltip'  => 'Number of user requests to reset the password.',
    'UserDetails_auth_error_count'              => 'HTTP error',
    'UserDetails_auth_error_count_tooltip'      => 'Indicator of HTTP errors that user requests produced.',
    'UserDetails_off_hours_login_count'         => 'Off-hours login',
    'UserDetails_off_hours_login_count_tooltip' => 'Number of user\'s login attempts during night time.',
    'UserDetails_avg_event_count'               => 'Average events',
    'UserDetails_avg_event_count_tooltip'       => 'Average number of events performed by user.',
    'UserDetails_ip_count'                      => 'Login attempts',
    'UserDetails_ip_count_tooltip'              => 'Total number of login attempts by user.',
    'UserDetails_session_count'                 => 'Sessions',
    'UserDetails_session_count_tooltip'         => 'Number of user\'s sessions.',
    'UserDetails_day_card_title'                => 'Today\'s activity',
    'UserDetails_day_card_title_tooltip'        => 'Today since midnight.',
    'UserDetails_week_card_title'               => 'Average daily activity',
    'UserDetails_week_card_title_tooltip'       => 'Median number of user events per day (last 7 days).',
];
