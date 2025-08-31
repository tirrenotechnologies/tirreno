<?php

namespace Controllers\Admin\Rules\Set;

class A05 extends BaseRule {
    public const NAME = 'Password change on new device';
    public const DESCRIPTION = 'User changed their password on new device, which can be a sign of account takeover.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $passwordChangeOnNewDevice = false;
        $passwordChange = \Utils\Constants::get('ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID');

        if ($params['eup_device_count'] > 1) {
            foreach (array_keys($params['event_device']) as $idx) {
                if ($params['event_type'][$idx] === $passwordChange && \Utils\Rules::eventDeviceIsNew($params, $idx)) {
                    $passwordChangeOnNewDevice = true;
                    break;
                }
            }
        }

        $params['event_password_change_on_new_device'] = $passwordChangeOnNewDevice;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_password_change_on_new_device']->equalTo(true),
        );
    }
}
