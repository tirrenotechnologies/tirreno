<?php

namespace CoreRules;

class C16 extends \Assets\Rule {
    public const NAME = 'Japan IP address';
    public const DESCRIPTION = 'IP address located in Japan.';
    public const ATTRIBUTES = ['ip'];

    protected function prepareParams(array $params): array {
        $params['eip_has_specific_country'] = in_array(\Utils\Constants::get('COUNTRY_CODE_JAPAN'), $params['eip_country_id']);

        return $params;
    }

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['eip_has_specific_country']->equalTo(true),
        );
    }
}
