<?php

namespace Controllers\Admin\Rules\Set;

class A02 extends BaseRule {
    public const NAME = 'Login failed on new device';
    public const DESCRIPTION = 'User failed to login with new device, which can be a sign of account takeover.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $suspiciousLoginFailed = false;
        $loginFail = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_LOGIN_FAIL');

        foreach ($params['event_type'] as $idx => $event) {
            if ($event === $loginFail && \Utils\Rules::eventDeviceIsNew($params, $idx)) {
                $suspiciousLoginFailed = true;
                break;
            }
        }

        $params['event_failed_login_on_new_device'] = $suspiciousLoginFailed;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_failed_login_on_new_device']->equalTo(true),
        );
    }
}
