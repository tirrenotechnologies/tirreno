<?php

namespace Controllers\Admin\Rules\Set;

class C12 extends BaseRule {
    public const NAME = 'European IP address';
    public const DESCRIPTION = 'IP address located in Europe Union.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $common = array_intersect(\Utils\Constants::get('COUNTRY_CODES_EUROPE'), $params['eip_country_id']);
        $params['eip_has_specific_country'] = (bool) count($common);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
