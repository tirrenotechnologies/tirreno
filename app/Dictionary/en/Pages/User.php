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
    'user_page_title' => 'Entity',
    'user_breadcrumb_title' => 'Entity',

    'user_widgets_id' => 'Entity',
    'user_widgets_id_tooltip' => 'Basic entity account information.',
    'user_widgets_ips_warning' => 'IP addresses',
    'user_widgets_ips_warning_tooltip' => 'A list of warning signals based on IP addresses linked to the entity.',
    'user_widgets_totals_warning' => 'Summary',
    'user_widgets_totals_warning_tooltip' => 'Total counts of unique identifiers and actions associated with this entity account.',
    'user_widgets_email' => 'Email',
    'user_widgets_email_tooltip' => 'A list of warning signals based on email addresses linked to the entity.',
    'user_widgets_domain' => 'Domain',
    'user_widgets_domain_tooltip' => 'A list of warning signals based on email domains linked to the entity.',
    'user_widgets_phone' => 'Phone',
    'user_widgets_phone_tooltip' => 'Phone.',

    'user_counters_total_new_devices' => 'New devices per day',
    'user_counters_total_new_devices_tooltip' => 'Total new devices over entity\'s sessions per day.',
    'user_counters_total_new_ips' => 'New IPs per day',
    'user_counters_total_new_ips_tooltip' => 'Total new IPs over entity\'s sessions per day.',
    'user_counters_total_events_max' => 'Events per session',
    'user_counters_total_events_max_tooltip' => 'Average total events over entity\'s sessions per day.',
    'user_counters_total_sessions' => 'Sessions per day',
    'user_counters_total_sessions_tooltip' => 'Total entity\'s sessions per day.',

    'user_recalculate_risk_score_success_message' => 'Entity trust score was successfully recalculated.',
    'user_recalculate_risk_score_tooltip' => 'Recalculate trust score',

    'user_remove_user_button' => 'Delete entity',
    'user_scheduled_for_removal' => 'This entity\'s data is scheduled for removal.',

    'user_review_comment_placeholder' => 'There is no review for this entity.',

    'payload_table_title' => 'Payload',
    'payload_table_title_tooltip' => 'Payload',
];
