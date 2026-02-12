<?php

namespace Tirreno\Rules\Core;

class A06 extends \Tirreno\Assets\Rule {
    public const NAME = 'Password change in new country';
    public const DESCRIPTION = 'User changed their password in new country, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $pwdChangeInNewCountry = false;
        $pwdChange = \Tirreno\Utils\Constants::get()->ACCOUNT_PASSWORD_CHANGE_EVENT_TYPE_ID;

        if (count(array_unique($params['eip_country_id'])) > 1) {
            foreach ($params['event_type'] as $idx => $event) {
                if ($event === $pwdChange) {
                    if (\Tirreno\Utils\Rules::countryIsNewByIpId($params, $params['event_ip'][$idx])) {
                        $pwdChangeInNewCountry = true;
                        break;
                    }
                }
            }
        }

        $params['event_password_change_in_new_country'] = $pwdChangeInNewCountry;

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['event_password_change_in_new_country']->equalTo(true),
        );
    }
}
