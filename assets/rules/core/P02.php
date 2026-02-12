<?php

namespace Tirreno\Rules\Core;

class P02 extends \Tirreno\Assets\Rule {
    public const NAME = 'Phone country mismatch';
    public const DESCRIPTION = 'Phone number country is not among the countries from which user has logged in. May be a sign of invalid phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function prepareParams(array $params): array {
        $params['lp_country_code_in_eip_country_id'] = \Tirreno\Utils\Rules::checkPhoneCountryMatchIp($params);

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['lp_country_code_in_eip_country_id']->equalTo(false),
        );
    }
}
