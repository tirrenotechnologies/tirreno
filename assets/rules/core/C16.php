<?php

namespace Tirreno\Rules\Core;

class C16 extends \Tirreno\Assets\Rule {
    public const NAME = 'Japan IP address';
    public const DESCRIPTION = 'IP address located in Japan.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_has_specific_country'] = in_array(\Tirreno\Utils\Constants::get()->COUNTRY_CODE_JAPAN, $params['eip_country_id']);

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
