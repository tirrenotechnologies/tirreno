<?php

namespace Controllers\Admin\Rules\Set;

class A06 extends BaseRule {
    public const NAME = 'Password change in new country';
    public const DESCRIPTION = 'User changed their password in new country, which can be a sign of account takeover.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $pwdChangeInNewCountry = false;
        $pwdChange = \Utils\Constants::get('EVENT_TYPE_ID_ACCOUNT_PASSWORD_CHANGE');

        if (count(array_unique($params['eip_country_id'])) > 1) {
            foreach ($params['event_type'] as $idx => $event) {
                if ($event === $pwdChange) {
                    if (\Utils\Rules::countryIsNewByIpId($params, $params['event_ip'][$idx])) {
                        $pwdChangeInNewCountry = true;
                        break;
                    }
                }
            }
        }

        $params['event_password_change_in_new_country'] = $pwdChangeInNewCountry;

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['event_password_change_in_new_country']->equalTo(true),
        );
    }
}
