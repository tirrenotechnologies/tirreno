<?php

namespace Controllers\Admin\Rules\Set;

class A07 extends BaseRule {
    public const NAME = 'Password change in new subnet';
    public const DESCRIPTION = 'User changed their password in new subnet, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $passwordChangeInNewCidr = false;
        $passwordChange = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE');

        if ($params['eip_unique_cidrs'] > 1) {
            foreach ($params['event_type'] as $idx => $event) {
                if ($event === $passwordChange && \Utils\Rules::cidrIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $passwordChangeInNewCidr = true;
                    break;
                }
            }
        }

        $params['event_password_change_in_new_cidr'] = $passwordChangeInNewCidr;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_password_change_in_new_cidr']->equalTo(true),
        );
    }
}
