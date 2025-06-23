<?php

namespace Controllers\Admin\Rules\Set;

class C01 extends BaseRule {
    public const NAME = 'Nigeria IP address';
    public const DESCRIPTION = 'IP address located in Nigeria. This region is associated with a higher risk.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_has_specific_country'] = in_array(\Utils\Constants::get('COUNTRY_CODE_NIGERIA'), $params['eip_country_id']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
