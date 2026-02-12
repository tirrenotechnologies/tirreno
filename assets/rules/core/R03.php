<?php

namespace Tirreno\Rules\Core;

class R03 extends \Tirreno\Assets\Rule {
    public const NAME = 'Phone in blacklist';
    public const DESCRIPTION = ' This phone number appears in the blacklist.';
    public const ATTRIBUTES = [];

    protected function defineCondition(): \Ruler\Operator\LogicalOperator {
        return $this->rb->logicalAnd(
            $this->rb['lp_fraud_detected']->equalTo(true),
        );
    }
}
