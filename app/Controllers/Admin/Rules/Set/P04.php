<?php

namespace Controllers\Admin\Rules\Set;

class P04 extends BaseRule {
    public const NAME = 'Valid phone';
    public const DESCRIPTION = 'User provided correct phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['lp_invalid_phone']->equalTo(false),
        );
    }
}
