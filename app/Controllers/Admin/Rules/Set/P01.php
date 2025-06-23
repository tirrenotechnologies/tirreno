<?php

namespace Controllers\Admin\Rules\Set;

class P01 extends BaseRule {
    public const NAME = 'Invalid phone format';
    public const DESCRIPTION = 'User provided incorrect phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['lp_invalid_phone']->equalTo(true),
        );
    }
}
