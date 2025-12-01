<?php

namespace CoreRules;

class P01 extends \Assets\Rule {
    public const NAME = 'Invalid phone format';
    public const DESCRIPTION = 'User provided incorrect phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['lp_invalid_phone']->equalTo(true),
            $this->rb['ep_phone_number']->notEqualTo([]),
        );
    }
}
