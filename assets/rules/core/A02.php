<?php

namespace Tirreno\Rules\Core;

class A02 extends \Tirreno\Assets\Rule {
    public const NAME = 'Login failed on new device';
    public const DESCRIPTION = 'User failed to login with new device, which can be a sign of account takeover.';
    public const ATTRIBUTES = [];

    protected function prepareParams(array $params): array {
        $suspiciousLoginFailed = false;
        $loginFail = \Tirreno\Utils\Constants::get()->ACCOUNT_LOGIN_FAIL_EVENT_TYPE_ID;

        foreach ($params['event_type'] as $idx => $event) {
            if ($event === $loginFail && \Tirreno\Utils\Rules::eventDeviceIsNew($params, $idx)) {
                $suspiciousLoginFailed = true;
                break;
            }
        }

        $params['event_failed_login_on_new_device'] = $suspiciousLoginFailed;

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_failed_login_on_new_device']->equalTo(true),
        );
    }
}
