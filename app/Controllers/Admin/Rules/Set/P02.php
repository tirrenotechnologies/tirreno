<?php

namespace Controllers\Admin\Rules\Set;

class P02 extends BaseRule {
    public const NAME = 'Phone country mismatch';
    public const DESCRIPTION = 'Phone number country is not among the countries from which user has logged in. May be a sign of invalid phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function prepareParams(array $params): array {
        $params['lp_country_code_in_eip_country_id'] = \Utils\Rules::checkPhoneCountryMatchIp($params);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['lp_country_code_in_eip_country_id']->equalTo(false),
        );
    }
}
