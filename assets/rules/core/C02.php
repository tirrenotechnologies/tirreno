<?php

namespace Tirreno\Rules\Core;

class C02 extends \Tirreno\Assets\Rule {
    public const NAME = 'India IP address';
    public const DESCRIPTION = 'IP address located in India. This region is associated with a higher risk.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_has_specific_country'] = in_array(\Tirreno\Utils\Constants::get()->COUNTRY_CODE_INDIA, $params['eip_country_id']);

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
