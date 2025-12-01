<?php

namespace CoreRules;

class P04 extends \Assets\Rule {
    public const NAME = 'Valid phone';
    public const DESCRIPTION = 'User provided correct phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['lp_invalid_phone']->equalTo(false),
            $this->rb['ep_phone_number']->notEqualTo([]),
        );
    }
}
