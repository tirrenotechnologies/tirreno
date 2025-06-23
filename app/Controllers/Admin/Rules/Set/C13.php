<?php

namespace Controllers\Admin\Rules\Set;

class C13 extends BaseRule {
    public const NAME = 'North America IP address';
    public const DESCRIPTION = 'IP address located in Canada or USA.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $common = array_intersect(\Utils\Constants::get('COUNTRY_CODES_NORTH_AMERICA'), $params['eip_country_id']);
        $params['eip_has_specific_country'] = (bool) count($common);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
