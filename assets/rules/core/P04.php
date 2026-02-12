<?php

namespace Tirreno\Rules\Core;

class P04 extends \Tirreno\Assets\Rule {
    public const NAME = 'Valid phone';
    public const DESCRIPTION = 'User provided correct phone number.';
    public const ATTRIBUTES = ['phone'];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['lp_invalid_phone']->equalTo(false),
            $this->rb['ep_phone_number']->notEqualTo([]),
        );
    }
}
