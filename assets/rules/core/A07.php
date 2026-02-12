<?php

namespace Tirreno\Rules\Core;

class A07 extends \Tirreno\Assets\Rule {
    public const NAME = 'Password change in new subnet';
    public const DESCRIPTION = 'User changed their password in new subnet, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $passwordChangeInNewCidr = false;
        $passwordChange = \Tirreno\Utils\Constants::get()->ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID;

        if ($params['eip_unique_cidrs'] > 1) {
            foreach ($params['event_type'] as $idx => $event) {
                if ($event === $passwordChange && \Tirreno\Utils\Rules::cidrIsNewByIpId($params, $params['event_ip'][$idx])) {
                    $passwordChangeInNewCidr = true;
                    break;
                }
            }
        }

        $params['event_password_change_in_new_cidr'] = $passwordChangeInNewCidr;

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_password_change_in_new_cidr']->equalTo(true),
        );
    }
}
