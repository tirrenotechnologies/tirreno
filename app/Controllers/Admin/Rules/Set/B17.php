<?php

namespace Controllers\Admin\Rules\Set;

class B17 extends BaseRule {
    public const NAME = 'Single country';
    public const DESCRIPTION = 'IP addresses are located in a single country.';
    public const ATTRIBUTES = ['ip'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['ea_total_country']->equalTo(1),
        );
    }
}
