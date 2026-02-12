<?php

namespace Tirreno\Rules\Core;

class C12 extends \Tirreno\Assets\Rule {
    public const NAME = 'European IP address';
    public const DESCRIPTION = 'IP address located in Europe Union.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $common = array_intersect(\Tirreno\Utils\Constants::get()->COUNTRY_CODES_EUROPE, $params['eip_country_id']);
        $params['eip_has_specific_country'] = (bool) count($common);

        return $params;
    }

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
