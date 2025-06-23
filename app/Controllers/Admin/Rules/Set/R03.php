<?php

namespace Controllers\Admin\Rules\Set;

class R03 extends BaseRule {
    public const NAME = 'Phone in blacklist';
    public const DESCRIPTION = ' This phone number appears in the blacklist.';
    public const ATTRIBUTES = [];

    protected function defineCondition() {
        return $this->rb->logicalAnd(
            $this->rb['lp_fraud_detected']->equalTo(true),
        );
    }
}
