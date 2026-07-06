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
    'settings_retentionPolicy_form_title' => 'Data retention',
    'settings_retentionPolicy_form_title_tooltip' => 'Configure the maximum duration of the recorded information storage.',
    'settings_retentionPolicy_form_button_save' => 'Update',

    'settings_retentionPolicy_form_field_policy_label' => 'Retention period',
    'settings_retentionPolicy_form_field_policy_warning' => 'Reducing the retention period will result in the removal of all data belonging to entities who haven\'t logged in beyond selected period.',
    'settings_retentionPolicy_changeTimezone_success_message' => 'Data retention period has been changed successfully.',
];
